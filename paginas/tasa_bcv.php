<?php
/**
 * Obtiene la tasa USD del BCV usando el endpoint de DolarVzla y maneja la estructura JSON anidada.
 * Utiliza cURL para una conexión más robusta.
 * * @return float La tasa USD. Retorna una tasa de respaldo si hay algún error.
 */
function obtenerTasaBCV_API_Anidada() {
    $url = 'https://api.dolarvzla.com/public/exchange-rate';
    $default_rate = 36.5; // Tasa de respaldo (DEFAULT_RATE)
    
    // --- 1. CONFIGURACIÓN E INICIO DE CÁPSULA (Implementación con cURL) ---
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response_json = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    // --- FIN CÁPSULA cURL ---

    // Manejo de Errores de Conexión/HTTP (Similar a requests.raise_for_status())
    if ($response_json === FALSE || $curl_error || $http_code !== 200) {
        error_log("Error de conexión/HTTP ({$http_code}): {$curl_error}. Usando tasa predeterminada de {$default_rate}.");
        return $default_rate;
    }

    // --- 2. PROCESAMIENTO JSON (Lógica Adaptada de Python) ---
    $data = json_decode($response_json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error JSON: Respuesta de la API no válida. Usando tasa predeterminada.");
        return $default_rate;
    }

    // Lógica Python: data.get('current', {}).get('usd')
    // Acceso seguro al campo anidado: $data['current']['usd']
    
    // Comprueba si existe el array 'current' y si dentro existe 'usd'
    if (isset($data['current']) && is_array($data['current']) && isset($data['current']['usd'])) {
        $rate_float = $data['current']['usd'];
        
        // Verifica si el valor obtenido es un número válido (int o float)
        if (is_numeric($rate_float) && $rate_float > 0) {
            $rate = (float)$rate_float;
            // opcional: Puedes usar error_log o tu propio logger (similar a api_logger.info)
            error_log("Tasa de DolarVzla obtenida con éxito: {$rate}"); 
            return $rate;
        } else {
            error_log("La API devolvió un campo 'usd' no numérico o inválido. Usando tasa predeterminada.");
            return $default_rate;
        }
    } else {
        error_log("La API no devolvió la estructura anidada esperada (current.usd). Usando tasa predeterminada.");
        return $default_rate;
    }
}

// Ejemplo de Uso:
$tasa_usd_actualizada = obtenerTasaBCV_API_Anidada();


?>