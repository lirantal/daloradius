# totp-php vendored snapshot

Vendored from: https://github.com/remotemerge/totp-php
Snapshot: `ca915f2da8676cb3a89c82d10d543883ea11d74a`
License: MIT License, copyright Madan Sapkota.

Only the runtime `src/` files are included. Demo, test, Docker, Composer, and CI files are intentionally omitted.

Local note: `AbstractTotp::packTimeSlice()` is patched to use explicit big-endian packing (`pack('N2', 0, $timeSlice)`) for RFC 6238 portability.
