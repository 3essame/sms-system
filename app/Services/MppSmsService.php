<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MppSmsService
{
    protected string $baseUrl;
    protected string $apikey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.mpp-sms.com/api/send.aspx';
        $this->apikey = 'fJZ6a2xnGt2zuYI2U0OurpLqD';
    }

    /**
     * Send SMS message
     *
     * @param string $phoneNumber
     * @param string $message
     * @return array
     */
    public function sendSms(string $phoneNumber, string $message): array
    {
        try {
            // تنظيف وتنسيق رقم الهاتف
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (!str_starts_with($phoneNumber, '965')) {
                $phoneNumber = '965' . $phoneNumber;
            }

            // تحضير عنوان URL
            $params = [
                'apikey' => $this->apikey,
                'language' => 2,
                'sender' => 'BAKKATRAVEL',
                'mobile' => $phoneNumber,
                'message' => $message
            ];

            $url = $this->baseUrl . '?' . http_build_query($params);

            Log::info('Attempting to send SMS', [
                'url' => str_replace($this->apikey, '****', $url),
                'phone' => $phoneNumber
            ]);

            // إعداد cURL
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_ENCODING => '',
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
            ]);
            
            // تنفيذ الطلب
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            
            // تسجيل التفاصيل
            Log::info('API Response', [
                'response' => $response,
                'http_code' => $httpCode,
                'curl_error' => $error,
                'curl_errno' => $errno
            ]);

            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'error' => 'خطأ في الاتصال: ' . $error,
                    'technical_details' => [
                        'curl_error' => $error,
                        'curl_errno' => $errno,
                        'http_code' => $httpCode
                    ]
                ];
            }

            // التحقق من الاستجابة
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => 'خطأ في الاستجابة: ' . $response,
                    'http_code' => $httpCode
                ];
            }

            $response = trim($response);
            if (is_numeric($response) || stripos($response, 'success') !== false) {
                return [
                    'success' => true,
                    'response' => $response
                ];
            }

            return [
                'success' => false,
                'error' => $response ?: 'فشل في إرسال الرسالة',
                'http_code' => $httpCode
            ];

        } catch (\Exception $e) {
            Log::error('SMS sending error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'خطأ في إرسال الرسالة: ' . $e->getMessage()
            ];
        }
    }
}
