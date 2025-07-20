// 🚫 Deshabilitar clic derecho
document.addEventListener('contextmenu', e => e.preventDefault());

// 🚫 Deshabilitar atajos comunes de DevTools
document.addEventListener('keydown', function(e) {
    const blocked = [
        { key: 'F12', keyCode: 123 },
        { key: 'I', ctrl: true, shift: true },
        { key: 'J', ctrl: true, shift: true },
        { key: 'C', ctrl: true, shift: true },
        { key: 'U', ctrl: true },
        { key: 'S', ctrl: true },
    ];
    for (let combo of blocked) {
        if (
            e.key.toUpperCase() === combo.key &&
            (!combo.ctrl || e.ctrlKey) &&
            (!combo.shift || e.shiftKey) &&
            (!combo.alt || e.altKey) &&
            (!combo.meta || e.metaKey)
        ) {
            e.preventDefault();
            return false;
        }
    }
});

// 🚫 Bloqueo de arrastre
document.addEventListener('dragstart', e => e.preventDefault());

// 🚨 Detectar apertura de DevTools por tamaño de ventana
(function detectResizeDevTools() {
    const threshold = 160;
    setInterval(() => {
        if (window.outerWidth - window.innerWidth > threshold || window.outerHeight - window.innerHeight > threshold) {
            triggerBlock();
        }
    }, 500);
})();

// 🕵️ Detectar apertura de consola con trampa de propiedad
(function detectDevToolsTrap() {
    let isOpen = false;
    const element = new Image();
    Object.defineProperty(element, 'id', {
        get() {
            isOpen = true;
            triggerBlock();
        }
    });
    setInterval(() => {
        isOpen = false;
        console.log(element);
        if (isOpen) {
            triggerBlock();
        }
    }, 1000);
})();

// ⚠️ Detectar modo "debugger"
(function detectDebuggerLoop() {
    let start = performance.now();
    debugger;
    if (performance.now() - start > 50) {
        triggerBlock();
    }
    setInterval(() => {
        let time = performance.now();
        debugger;
        if (performance.now() - time > 50) {
            triggerBlock();
        }
    }, 1500);
})();

// 🛑 Acción al detectar DevTools
function triggerBlock() {
    document.body.innerHTML = "";
    alert("⚠️ Inspección de elementos detectada. Esta acción está prohibida.");
    setTimeout(() => {
        window.close(); // No siempre funciona
        window.location.href = "about:blank"; // Redirige a página en blanco
    }, 100);
}

// 🚫 Mensaje señuelo en consola
console.log('%c🔒 ACCESO DENEGADO 🔒', 'color: red; font-size: 48px; font-weight: bold;');
console.log('%cNo intentes inspeccionar esta página.', 'font-size: 20px;');


