"""No-network checks for the GIS Leaflet distribution and integration."""
import json
from pathlib import Path
import re
import unittest

ROOT = Path(__file__).resolve().parents[1]
ASSETS = ROOT / 'app/common/static/leaflet'


class LeafletAssetsTest(unittest.TestCase):
    def test_gis_pages_use_local_versioned_assets(self):
        for name in ('gis-viewmap.php', 'gis-editmap.php'):
            with self.subTest(page=name):
                page = ROOT / 'app/operators' / name
                source = page.read_text()
                for suffix in ('css', 'js'):
                    pattern = rf'\$extra_{suffix}\s*=\s*array\("([^"]+)"\)'
                    reference = re.search(pattern, source).group(1)
                    self.assertEqual(reference, f'static/leaflet/leaflet.{suffix}?v=1.9.4')
                    self.assertTrue((page.parent / reference.split('?')[0]).is_file())
                self.assertNotIn('unpkg.com/leaflet', source)
                self.assertIn('tile.openstreetmap.org', source)

    def test_distribution_version_and_source_map(self):
        source = (ASSETS / 'leaflet.js').read_text()
        self.assertIn('Leaflet 1.9.4', source)
        self.assertIn('sourceMappingURL=leaflet.js.map', source)
        mapping = json.loads((ASSETS / 'leaflet.js.map').read_text())
        self.assertEqual(mapping['version'], 3)
        self.assertTrue(mapping['sources'])
        self.assertEqual(len(mapping['sources']), len(mapping['sourcesContent']))
        self.assertTrue(all(mapping['sourcesContent']))

    def test_stylesheet_images_and_default_markers_exist(self):
        css = (ASSETS / 'leaflet.css').read_text()
        for name in re.findall(r'url\([\'"]?(images/[^)\'\"]+)', css):
            with self.subTest(css_image=name):
                self.assertTrue((ASSETS / name).is_file())
        for name in ('layers.png', 'layers-2x.png', 'marker-icon.png',
                     'marker-icon-2x.png', 'marker-shadow.png'):
            with self.subTest(image=name):
                self.assertTrue((ASSETS / 'images' / name).read_bytes().startswith(b'\x89PNG\r\n\x1a\n'))

    def test_license_and_provenance_are_shipped(self):
        license_text = (ASSETS / 'LICENSE').read_text()
        self.assertIn('Redistribution and use', license_text)
        self.assertIn('Volodymyr Agafonkin', license_text)
        readme = (ASSETS / 'README.md').read_text()
        self.assertIn('/releases/download/v1.9.4/leaflet.zip', readme)
        self.assertIn('OpenStreetMap tiles still require network access', readme)


if __name__ == '__main__':
    unittest.main()
