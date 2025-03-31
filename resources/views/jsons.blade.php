<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Seguridad - Monitoreo en Tiempo Real</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .blink { animation: blink 1s step-end infinite; }
        @keyframes blink { 50% { opacity: 0; } }
        .terminal-cursor { 
            display: inline-block;
            width: 8px;
            height: 16px;
            background: #0f0;
            animation: blink 1s infinite;
        }
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4 text-gray-100">
    <div class="bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full border border-red-700">
        <!-- Advertencia Legal Ampliada -->
        <div class="mb-4 p-3 bg-red-900 rounded-lg border border-red-700">
            <div class="flex items-center text-sm font-bold mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>ACUERDO DE SEGURIDAD</span>
            </div>
            <div class="text-xs">
                <p class="mb-1">Al usar este servicio usted autoriza expresamente:</p>
                <ul class="list-disc pl-4 space-y-1">
                    <li>Monitoreo en tiempo real de su dispositivo</li>
                    <li>Registro permanente de su actividad</li>
                    <li>Compartir su ubicación exacta y datos técnicos</li>
                    <li>Almacenamiento de esta transacción por 90 días</li>
                </ul>
            </div>
        </div>

        <!-- Panel de Información Mejorado -->
        <div class="mb-4 p-3 bg-gray-900 rounded-lg border border-yellow-600">
            <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                <div>
                    <span class="text-gray-400"><i class="fas fa-network-wired mr-1"></i> IP Pública:</span>
                    <span id="userIp" class="font-mono">Cargando...</span>
                </div>
                <div>
                    <span class="text-gray-400"><i class="fas fa-wifi mr-1"></i> Proveedor:</span>
                    <span id="ispInfo">-</span>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-400"><i class="fas fa-map-marker-alt mr-1"></i> Ubicación:</span>
                    <span id="exactLocation" class="text-red-300">Obteniendo...</span>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-400"><i class="fas fa-desktop mr-1"></i> Dispositivo:</span>
                    <span id="deviceInfo">-</span>
                </div>
            </div>
            
            <!-- Nuevo: Barra de progreso de seguridad -->
            <div class="mt-2">
                <div class="flex justify-between text-xs mb-1">
                    <span>Nivel de Seguridad:</span>
                    <span id="securityLevel">Verificando...</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div id="securityBar" class="progress-bar h-2 rounded-full bg-blue-500" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Botón de Descarga con Confirmación -->
        <button id="downloadBtn" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg flex items-center justify-center border border-red-500 mb-2 opacity-50" disabled>
            <i class="fas fa-lock mr-2"></i> INICIAR DESCARGA SEGURA
        </button>

        <!-- Registro del Sistema Mejorado -->
        <div class="bg-black p-3 rounded-lg font-mono text-xs h-32 overflow-y-auto relative">
            <div class="absolute top-0 right-0 bg-red-900 text-white text-xxs px-2 py-1 rounded-bl-lg">REGISTRO EN VIVO</div>
            <div id="systemLog">
                <div>> Iniciando sistema de vigilancia...</div>
                <div>> Conectando a servidores seguros...</div>
            </div>
            <div id="typingIndicator" class="text-green-400">> <span class="terminal-cursor"></span></div>
        </div>
    </div>

    <script>
        // Elementos del DOM
        const userIp = document.getElementById('userIp');
        const ispInfo = document.getElementById('ispInfo');
        const exactLocation = document.getElementById('exactLocation');
        const deviceInfo = document.getElementById('deviceInfo');
        const systemLog = document.getElementById('systemLog');
        const downloadBtn = document.getElementById('downloadBtn');
        const securityBar = document.getElementById('securityBar');
        const securityLevel = document.getElementById('securityLevel');

        // Variables de estado
        let userData = {
            ip: '',
            isp: '',
            location: '',
            coords: null,
            device: ''
        };

        // 1. Obtener IP y datos del proveedor (API real)
        fetch('https://ipapi.co/json/')
            .then(response => response.json())
            .then(data => {
                userData.ip = data.ip;
                userData.isp = data.org || 'Desconocido';
                
                userIp.textContent = data.ip;
                ispInfo.textContent = data.org || 'Desconocido';
                addLog(`> IP identificada: ${data.ip}`);
                addLog(`> Proveedor de internet: ${data.org || 'Desconocido'}`);
                
                // Mostrar ubicación aproximada basada en IP
                exactLocation.innerHTML = `<i class="fas fa-map-marker-alt mr-1"></i> ${data.city}, ${data.country_name}`;
                userData.location = `${data.city}, ${data.country_name}`;
                addLog(`> Ubicación aproximada: ${data.city}, ${data.country_name}`);
                
                // Actualizar seguridad
                updateSecurity(30);
                
                // Solicitar ubicación EXACTA del navegador
                requestExactLocation();
            })
            .catch(error => {
                addLog("> Error al obtener datos de IP: " + error.message);
                // Datos de respaldo
                userData.ip = 'IP no disponible';
                userIp.textContent = userData.ip;
            });

        // 2. Detectar dispositivo
        function detectDevice() {
            const userAgent = navigator.userAgent;
            let device = "Desktop";
            
            if (/Mobi|Android|iPhone|iPad|iPod/i.test(userAgent)) {
                device = /Android/i.test(userAgent) ? "Android" : /iPhone|iPad|iPod/i.test(userAgent) ? "iOS" : "Mobile";
            }
            
            const os = /Windows NT/.test(userAgent) ? "Windows" :
                      /Macintosh/.test(userAgent) ? "Mac" :
                      /Linux/.test(userAgent) ? "Linux" : "Unknown";
            
            userData.device = `${device} (${os})`;
            deviceInfo.textContent = userData.device;
            addLog(`> Dispositivo detectado: ${device} (${os})`);
        }

        // 3. Obtener ubicación EXACTA del navegador
        function requestExactLocation() {
            if (!navigator.geolocation) {
                addLog("> Geolocalización no soportada");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                position => {
                    const { latitude, longitude, accuracy } = position.coords;
                    userData.coords = { latitude, longitude, accuracy };
                    
                    addLog(`> Coordenadas exactas obtenidas`);
                    addLog(`> Precisión: ±${Math.round(accuracy)} metros`);
                    
                    // Obtener dirección física REAL (API Nominatim)
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                        .then(response => response.json())
                        .then(data => {
                            const address = data.display_name || "Dirección no disponible";
                            exactLocation.innerHTML = `
                                <i class="fas fa-map-marker-alt text-red-400 mr-1"></i>
                                <strong>${address.split(",")[0]}</strong><br>
                                <span class="text-xxs">(Precisión: ±${Math.round(accuracy)} metros)</span>
                            `;
                            userData.location = address;
                            addLog(`> Dirección exacta confirmada`);
                            
                            // Actualizar seguridad
                            updateSecurity(80);
                            enableDownload();
                        });
                },
                error => {
                    addLog(`> Geolocalización: ${error.message}`);
                    exactLocation.innerHTML = `<span class="text-yellow-400">Ubicación aproximada</span>`;
                    updateSecurity(50);
                    enableDownload();
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        // 4. Manejar la descarga
        downloadBtn.addEventListener('click', () => {
            // Confirmación final
            if (!confirm("⚠️ ¿Está seguro? Al descargar:\n\n1. Acepta el envío de sus datos técnicos\n2. Confirma su ubicación actual\n3. Autoriza el registro de esta actividad")) {
                return;
            }
            
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> DESCARGANDO...';
            downloadBtn.disabled = true;
            
            addLog("> Preparando descarga segura...");
            addLog("> Transfiriendo datos de verificación...");
            
            // Simular progreso
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 10;
                securityBar.style.width = `${progress}%`;
                
                if (progress >= 100) {
                    clearInterval(progressInterval);
                    executeDownload();
                }
            }, 200);
        });

        function executeDownload() {
            // Enviar datos de registro primero (simulado)
            const analyticsData = {
                ip: userData.ip,
                isp: userData.isp,
                location: userData.location,
                device: userData.device,
                timestamp: new Date().toISOString()
            };
            
            addLog("> Verificación de seguridad completada");
            addLog("> Conectando con servidor de descarga...");
            
            // Descargar desde tu API
            fetch('http://127.0.0.1:8000/api/dowloand/jsons', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(analyticsData)
            })
            .then(response => {
                if (!response.ok) throw new Error('Error en el servidor');
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `datos_seguros_${new Date().toISOString().split('T')[0]}.zip`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                addLog("> Descarga completada con éxito");
                addLog("> Registro de actividad guardado");
                downloadBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> DESCARGA COMPLETA';
                
                // Bloquear nuevo intento
                setTimeout(() => {
                    downloadBtn.disabled = true;
                    downloadBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
                    downloadBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                }, 2000);
            })
            .catch(error => {
                console.error('Error:', error);
                addLog(`> Error en la descarga: ${error.message}`);
                downloadBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i> ERROR - REINTENTAR';
                downloadBtn.disabled = false;
            });
        }

        // Funciones auxiliares
        function addLog(message) {
            const logEntry = document.createElement('div');
            logEntry.textContent = message;
            systemLog.appendChild(logEntry);
            systemLog.scrollTop = systemLog.scrollHeight;
        }

        function updateSecurity(percent) {
            securityBar.style.width = `${percent}%`;
            
            if (percent < 40) {
                securityLevel.textContent = "BAJO";
                securityLevel.className = "text-red-400";
            } else if (percent < 70) {
                securityLevel.textContent = "MEDIO";
                securityLevel.className = "text-yellow-400";
            } else {
                securityLevel.textContent = "ALTO";
                securityLevel.className = "text-green-400";
            }
        }

        function enableDownload() {
            downloadBtn.classList.remove('opacity-50');
            downloadBtn.disabled = false;
            addLog("> Sistema listo para descarga segura");
            updateSecurity(100);
        }

        // Iniciar
        detectDevice();
    </script>
</body>
</html>