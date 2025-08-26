<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\EmailImage;

class TestController extends Controller
{
    public function testClick()
    {
        $testEmail = 'test@example.com';
        $testEmailEncoded = base64_encode($testEmail);
        
        return view('test_redirects', [
            'testEmail' => $testEmail,
            'testEmailEncoded' => $testEmailEncoded,
            'imageId' => 1
        ]);
    }

    public function testMobileRedirect()
    {
        $userAgent = request()->userAgent();
        $isMobile = $this->isMobileDevice($userAgent);
        
        return response()->json([
            'user_agent' => $userAgent,
            'is_mobile' => $isMobile,
            'mobile_keywords_found' => $this->getMobileKeywords($userAgent),
            'current_url' => request()->url(),
            'full_url' => request()->fullUrl()
        ]);
    }

    public function testRedirectWithParams($id_img = 1, $email = 'test@example.com')
    {
        $userAgent = request()->userAgent();
        $isMobile = $this->isMobileDevice($userAgent);
        
        // Obtener la URL de redirección de la imagen
        $image = EmailImage::find($id_img);
        $redirectUrl = $image && $image->link_redirection ? $image->link_redirection : 'https://www.google.com';
        
        Log::info('TestController: Probando redirección', [
            'id_img' => $id_img,
            'email' => $email,
            'is_mobile' => $isMobile,
            'user_agent' => $userAgent,
            'redirect_url' => $redirectUrl
        ]);

        if ($isMobile) {
            return view('mobile_redirect', [
                'redirectUrl' => $redirectUrl,
                'email' => $email,
                'imageId' => $id_img
            ]);
        } else {
            return redirect($redirectUrl);
        }
    }

    public function testDeviceDetection()
    {
        $userAgent = request()->userAgent();
        $isMobile = $this->isMobileDevice($userAgent);
        $mobileKeywords = $this->getMobileKeywords($userAgent);
        
        return view('test_device_detection', [
            'userAgent' => $userAgent,
            'isMobile' => $isMobile,
            'mobileKeywords' => $mobileKeywords,
            'currentUrl' => request()->url(),
            'fullUrl' => request()->fullUrl()
        ]);
    }

    private function isMobileDevice($userAgent)
    {
        if (!$userAgent) return false;
        
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone', 
            'BlackBerry', 'Opera Mini', 'IEMobile', 'Mobile Safari',
            'CriOS', 'FxiOS', 'OPiOS', 'Vivaldi'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function getMobileKeywords($userAgent)
    {
        if (!$userAgent) return [];
        
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone', 
            'BlackBerry', 'Opera Mini', 'IEMobile', 'Mobile Safari',
            'CriOS', 'FxiOS', 'OPiOS', 'Vivaldi'
        ];
        
        $found = [];
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                $found[] = $keyword;
            }
        }
        
        return $found;
    }
} 