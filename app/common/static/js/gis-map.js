/* Keep the current geographic center and zoom when the map container resizes. */
(function () {
    'use strict';

    window.daloObserveMapResize = function (map) {
        var frame = null;
        var observer = null;

        function resize() {
            if (frame !== null) {
                return;
            }
            frame = window.requestAnimationFrame(function () {
                frame = null;
                // Leaflet's non-animated pan compensates for the new pixel center.
                // Do not fitBounds here: preserve the operator's chosen view.
                map.invalidateSize({animate: false, pan: true, debounceMoveend: true});
            });
        }

        if (typeof window.ResizeObserver === 'function') {
            observer = new window.ResizeObserver(resize);
            observer.observe(map.getContainer());
        } else {
            window.addEventListener('resize', resize);
        }

        map.on('unload', function () {
            if (observer) {
                observer.disconnect();
            } else {
                window.removeEventListener('resize', resize);
            }
            if (frame !== null) {
                window.cancelAnimationFrame(frame);
                frame = null;
            }
        });
    };
}());
