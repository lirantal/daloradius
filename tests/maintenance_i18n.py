#!/usr/bin/env python3
"""Check that every operator locale fully translates maintenance UI keys.

Run: python3 tests/maintenance_i18n.py
This is a static source check and does not require PHP or Docker.
"""
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]
LANG = ROOT / 'app/operators/lang'
SCALAR_ENTRY = re.compile(r"^\s*'([^']+)'\s*=>\s*'((?:\\.|[^'])*)',\s*$")
NOWDOC_ENTRY = re.compile(r"^\s*'([^']+)'\s*=>\s*<<<'([A-Z][A-Z0-9_]*)'\s*$")
PLACEHOLDER = re.compile(r'%(?:\d+\$)?[sd]')
TAG = re.compile(r'</?([a-zA-Z0-9]+)(?:\s+[^>]*)?>')


def maintenance_block(locale):
    text = (LANG / f'{locale}.php').read_text()
    marker = "$l['maintenance'] = ["
    assert text.count(marker) == 1, f'{locale}: expected one maintenance block'
    return text.split(marker, 1)[1].split('];', 1)[0]


def entries(locale):
    block = maintenance_block(locale)
    values = {}
    lines = iter(block.splitlines())
    for line in lines:
        if match := SCALAR_ENTRY.match(line):
            values[match.group(1)] = match.group(2)
            continue
        if match := NOWDOC_ENTRY.match(line):
            key, label = match.groups()
            content = []
            for nowdoc_line in lines:
                if nowdoc_line.strip() == f'{label},':
                    break
                content.append(nowdoc_line)
            else:
                raise AssertionError(f'{locale}.{key}: unterminated nowdoc {label}')
            values[key] = '\n'.join(content)
    assert values, f'{locale}: maintenance block is empty or unparsable'
    return block, values


def main():
    locales = sorted(path.stem for path in LANG.glob('*.php')
                     if path.stem not in {'en', 'main'})
    _, english = entries('en')
    expected_keys = list(english)
    assert len(expected_keys) == 45, f'en: expected 45 keys, found {len(expected_keys)}'

    for locale in locales:
        block, translated = entries(locale)
        assert list(translated) == expected_keys, (
            f'{locale}: maintenance keys/order differ from English; '
            f'missing={set(english) - set(translated)}, extra={set(translated) - set(english)}'
        )
        assert 'CleanupStaleSessions' not in block, f'{locale}: title aliases the legacy stale-session label'
        assert translated['title'] != english['title'], f'{locale}: title still falls back to English'
        for key, source in english.items():
            value = translated[key]
            assert value, f'{locale}.{key}: empty translation'
            assert PLACEHOLDER.findall(value) == PLACEHOLDER.findall(source), (
                f'{locale}.{key}: printf placeholders differ from English'
            )
            assert TAG.findall(value) == TAG.findall(source), (
                f'{locale}.{key}: HTML tag structure differs from English'
            )
        print(f'PASS: {locale} has all {len(expected_keys)} maintenance translations')


if __name__ == '__main__':
    main()
