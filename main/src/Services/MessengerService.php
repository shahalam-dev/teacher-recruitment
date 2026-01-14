<?php

class MessengerService {
    /**
     * Sends a message via an external API using cURL.
     *
     * @param string $apiEndpoint The API URL.
     * @param string $apiKey      The API Key for authentication.
     * @param string $phone       The recipient's phone number.
     * @param string $message     The message content.
     * @return bool               True on success, False on failure.
     */
    public static function send($apiEndpoint, $apiKey, $phone, $message) {
        // Basic validation
        if (empty($apiEndpoint) || empty($phone) || empty($message)) {
            error_log("MessengerService: Missing required parameters.");
            return false;
        }

        // Prepare payload - specific for 360messenger v2
        $data = [
            'phonenumber' => $phone,
            'text'        => $message
        ];

        // Initialize cURL
        $ch = curl_init($apiEndpoint);
        
        // Configure cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        
        // Add Authorization Header
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey"
        ]);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        
        curl_close($ch);

        // Check for cURL errors
        if ($error) {
            error_log("MessengerService cURL Error: " . $error);
            return false;
        }

        // Check HTTP status code (200-299 considered success)
        if ($httpCode >= 200 && $httpCode < 300) {
            // Optional: Parse response if needed to confirm success 'status' in JSON
            return true;
        }

        // DEBUG: Echo the error to the screen so the user can see it
        echo "API Error [$httpCode]: " . $response . "<br>\n";
        error_log("MessengerService API Failed: HTTP $httpCode - Response: $response");
        return false;
    }
}
