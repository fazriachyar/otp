            $redis = new Client();
            $value = $redis->hgetall("staff:".$this->get('security.token_storage')->getToken()->getUser()->getId());
            $checkResult = self::verifyCode($value['secret'], $data['otp'],1);

/**
     * @Route("/banktransfer/otp")
     * @Method("GET")
     * @Security("has_role('banktransferlistinfo_otp')")
     */
    public function generateOtpAction(){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $secret = self::createSecret();
            $otp = self::getCode($secret);

            $redis = new Client();
			$redis->hset("staff:".$this->get('security.token_storage')->getToken()->getUser()->getId(),"secret", $secret);
            $redis->expire("staff:".$this->get('security.token_storage')->getToken()->getUser()->getId(), 30);
            $message['response']['success'] = "Generate Success!";
            $message['response']['otp'] = $otp;
            $em->getConnection()->commit();
        }
        catch (Throwable $e) {
            $em->getConnection()->rollBack();
            $error = $e->getPrevious();
            if($error){
              $messageError = $error->errorInfo[2];
            }
            else {
              $messageError = $e->getMessage();
            }
            $message['response']['failed'] = $messageError;
        }
        return new Response($this->get('jms_serializer')->serialize($message, 'json'),200);
    }

    public function createSecret(){
        $secret = '';
        $secretLength = 16;
        $validChars = self::validChars();
        $rnd = random_bytes($secretLength);

        for ($i = 0; $i < $secretLength; ++$i) {
            $secret .= $validChars[ord($rnd[$i]) & 31];
        }

        return $secret;
    }

    protected function validChars(){
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
            'Y', 'Z', '2', '3', '4', '5', '6', '7',
            '='
        );
    }

    protected function decodeSecret($secret){
        if (empty($secret)) {
            return '';
        }

        $base32chars = $this->validChars();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }
        for ($i = 0; $i < 4; ++$i) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
                return false;
            }
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; ++$j) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }

    public function getCode($secret, $timeSlice = null){
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->decodeSecret($secret);
        $codeLength = 6;

        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $codeLength);

        return str_pad($value % $modulo, $codeLength, '0', STR_PAD_LEFT);
    }

    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null){
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        if (strlen($code) != 6) {
            return false;
        }

        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }
