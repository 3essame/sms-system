<?php

namespace App\Http\Controllers;

use App\Services\MppSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(MppSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        return view('sms.index');
    }

    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:160'
        ]);

        try {
            $result = $this->smsService->sendSms(
                $request->phone,
                $request->message
            );

            Log::info('SMS sending attempt', [
                'phone' => $request->phone,
                'result' => $result
            ]);

            if (!$result['success']) {
                return back()
                    ->with('error', $result['error'] ?? 'حدث خطأ أثناء إرسال الرسالة')
                    ->with('debug', $result);
            }

            return back()
                ->with('success', 'تم إرسال الرسالة بنجاح')
                ->with('debug', $result);

        } catch (\Exception $e) {
            Log::error('SMS sending error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage())
                ->with('debug', ['error' => $e->getMessage()]);
        }
    }
}
