#!/usr/bin/env python3
"""Authenticated HTTP regression checks for the total-users chart.

Run against a disposable installation with these environment variables:
DALO_BASE_URL, DALO_ADMIN_USER/PASSWORD, DALO_ALLOWED_USER/PASSWORD,
DALO_DENIED_USER/PASSWORD, DALO_EXPECTED_USERS.
The allowed operator must have only mng_list_all granted; the denied operator
must have mng_list_all denied. Provision the expected RADIUS users separately.
This suite does not modify database records or print credentials/cookies.
"""
import http.cookiejar
import json
import os
import unittest
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser

ENDPOINT = '/library/graphs/total_users.php'


class LoginFields(HTMLParser):
    def __init__(self):
        super().__init__()
        self.token = None

    def handle_starttag(self, tag, attributes):
        attributes = dict(attributes)
        if tag == 'input' and attributes.get('name') == 'csrf_token':
            self.token = attributes.get('value')


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, request, fp, code, message, headers, url):
        return None


class Client:
    def __init__(self, base):
        self.base = base.rstrip('/')
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(
            urllib.request.HTTPCookieProcessor(self.cookies), NoRedirect())

    def request(self, path, data=None):
        if data is not None:
            data = urllib.parse.urlencode(data).encode()
        try:
            response = self.opener.open(self.base + path, data=data, timeout=15)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            return response.code, response.headers, response.read().decode()

    def login(self, user, password):
        status, _, body = self.request('/login.php')
        if status != 200:
            raise AssertionError('Login page did not return HTTP 200')
        fields = LoginFields()
        fields.feed(body)
        if not fields.token:
            raise AssertionError('Login CSRF token missing')
        status, headers, _ = self.request('/dologin.php', {
            'operator_user': user, 'operator_pass': password,
            'location': 'default', 'csrf_token': fields.token,
            'operator_auth_source': 'local',
        })
        if status != 302 or 'login.php' in headers.get('Location', ''):
            raise AssertionError('Operator login failed')
        status, _, _ = self.request('/home-main.php')
        if status != 200:
            raise AssertionError('Operator session was not authenticated')


class TotalUsersChartHTTP(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.base = os.environ['DALO_BASE_URL']
        cls.expected = int(os.environ['DALO_EXPECTED_USERS'])
        for role in ('admin', 'allowed', 'denied'):
            client = Client(cls.base)
            client.login(os.environ['DALO_' + role.upper() + '_USER'],
                         os.environ['DALO_' + role.upper() + '_PASSWORD'])
            setattr(cls, role, client)

    def assert_chart(self, client):
        status, headers, body = client.request(ENDPOINT)
        self.assertEqual(status, 200, 'Total-users chart must return HTTP 200')
        self.assertTrue(headers.get('Content-Type', '').startswith('application/json'))
        chart = json.loads(body)
        self.assertEqual(chart['type'], 'bar')
        self.assertEqual(chart['data']['datasets'][0]['data'], [self.expected])

    def test_seeded_administrator_can_read_chart(self):
        self.assert_chart(self.admin)

    def test_operator_with_only_user_list_permission_can_read_chart(self):
        self.assert_chart(self.allowed)

    def test_operator_without_user_list_permission_is_denied(self):
        status, _, body = self.denied.request(ENDPOINT)
        self.assertEqual(status, 403)
        self.assertEqual(json.loads(body), {'error': 'Widget access denied'})

    def test_anonymous_request_redirects_to_login(self):
        status, headers, _ = Client(self.base).request(ENDPOINT)
        self.assertEqual(status, 302)
        self.assertIn('login.php', headers.get('Location', ''))

    def test_malformed_filter_is_rejected(self):
        status, _, body = self.admin.request(ENDPOINT + '?username%5B%5D=invalid')
        self.assertEqual(status, 400)
        self.assertEqual(json.loads(body), {'error': 'Invalid widget filters'})

    def test_both_landing_pages_reference_the_chart(self):
        for page in ('/mng-main.php', '/mng-users.php'):
            with self.subTest(page=page):
                status, _, body = self.admin.request(page)
                self.assertEqual(status, 200)
                self.assertIn('library/graphs/total_users.php', body)
                self.assert_chart(self.admin)


if __name__ == '__main__':
    unittest.main(verbosity=2)
