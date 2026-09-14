#!/usr/bin/env python3
"""Chromium regression against PHP-rendered preview HTML and real app assets.

Export HTML from the disposable HTTP suite with MAINTENANCE_PREVIEW_HTML,
then run: python3 tests/acct_maintenance_browser.py /path/to/preview.html
Requires playwright and its Chromium browser. Network requests are intercepted;
this tests real browser rendering/JS, not live login or native autofill.
"""
from pathlib import Path
import sys
from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parents[1]


def main():
    html = Path(sys.argv[1]).read_bytes()
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch()
        page = browser.new_page()
        errors = []
        page.on('pageerror', lambda error: errors.append(str(error)))

        def serve(route):
            path = route.request.url.removeprefix('http://maintenance.test/')
            if path == 'preview':
                route.fulfill(body=html, content_type='text/html')
            else:
                asset = ROOT / 'app/operators' / path
                if asset.is_file() and asset.resolve().is_relative_to(ROOT.resolve()):
                    route.fulfill(path=str(asset))
                else:
                    route.fulfill(status=404, body='')

        page.route('http://maintenance.test/**', serve)

        def load():
            page.goto('http://maintenance.test/preview')
            page.wait_for_timeout(300)
            assert page.locator('#maintenance-preview').is_visible()

        for width in (390, 1280):
            page.set_viewport_size({'width': width, 'height': 900})
            load()
            assert page.locator('#maintenance-scope.card').is_visible()
            assert page.locator('#maintenance-preview.card').is_visible()
            confirm_button = page.locator('#maintenance-confirm button')
            assert 'btn-danger' in confirm_button.get_attribute('class').split()
            assert confirm_button.inner_text() == 'Close 1 session'
            assert page.locator('#preview-details').is_hidden()
            page.get_by_role('button', name='Preview details').click()
            assert page.locator('#preview-details').is_visible()
            numeric_headers = page.locator('#maintenance-preview th.text-end')
            assert numeric_headers.count() == 4
            for event in ('input', 'change'):
                load()
                page.locator('#close-value').dispatch_event(event)
                assert page.locator('#maintenance-preview').is_visible(), 'Unchanged filter erased preview'
                page.locator('#delete-value').dispatch_event(event)
                assert page.locator('#maintenance-preview').is_visible(), 'Inactive filter erased preview'
            load()
            page.locator('#close-value').fill('changed-filter')
            assert page.locator('#maintenance-preview').count() == 0
            load()
            page.locator('#close-scope').select_option('date')
            assert page.locator('#maintenance-preview').count() == 0
            load()
            page.locator('#delete-tab').click()
            assert page.locator('#maintenance-preview').count() == 0
            load()
            page.locator('#close-tab').click()
            assert page.locator('#maintenance-preview').is_visible()
            print(f'PASS: Chromium {width}px unchanged/inactive events retain preview; real edits and tab switch invalidate')
        assert not errors, errors
        browser.close()


if __name__ == '__main__':
    main()
