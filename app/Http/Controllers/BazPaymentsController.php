<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Validator;

class BazPaymentsController extends Controller
{
    public function createURL(Request $request)
    {
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()], 401);
        }

        $apiKey = config('services.baz.api_key');
        $apiSecret = config('services.baz.api_secret');
        $merchantId = config('services.baz.merchant_id');
        $url = config('services.baz.api_url');

        $payload = [
            "grant_type" => "client_credentials"
        ];

        $responseAuth = Http::asForm()->withHeaders([
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
        ])->post($url . 'tpv-virtual/servicios/socios-comerciales/oauth2/v1/token/', $payload);

        $bazAuthResponse = json_decode($responseAuth->getBody()->getContents());

        if (isset($bazAuthResponse->access_token)) {
            $responseAccessKeys = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bazAuthResponse->access_token,
            ])->get($url . 'tpv-virtual/servicios/socios-comerciales/seguridad/v1/aplicaciones/llaves');

            $accessKeysResponse = json_decode($responseAccessKeys->getBody()->getContents());

            if (isset($accessKeysResponse->resultado->idAcceso)) {
                $payloadPaymentURL = [
                    "idComercio" => $merchantId,
                    "enlaceRedireccion" => "https://dev1.mx",
                    "ordenPago" => [
                        "referencia" => "UNITECH-TEST-" . time(),
                        "monto" => $this->encrypt($accessKeysResponse->resultado->accesoPublico, "100.00"),
                        "codigoMoneda" => $this->encrypt($accessKeysResponse->resultado->accesoPublico, "MXN"),
                    ]
                ];

                $responseURL = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $bazAuthResponse->access_token,
                    'x-id-acceso' => $accessKeysResponse->resultado->idAcceso
                ])->post($url . 'tpv-virtual/servicios/socios-comerciales/pagos/v1/solicitudes/', $payloadPaymentURL);

                $urlResponse = json_decode($responseURL->getBody()->getContents());

                return response()->json(['success' => true, 'data' => $bazAuthResponse, 'keys' => $accessKeysResponse, 'url' => $urlResponse], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Error al procesar clave de acceso ante Banco Azteca', 'data' => $accessKeysResponse], 401);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Error al procesar token ante Banco Azteca', 'data' => $bazAuthResponse], 401);
        }
    }

    private function encrypt($publicKey, $string)
    {
        $result = shell_exec("python3 " . app_path() .
            "/utils/encrypt.py '" . $publicKey .
            "' '" . $string . "'");

        return $result;
    }
}
