<?php if (!defined('SITE')) exit('No direct script access allowed');

// we're going to rewrite this later

class Mail 
{ 
    public $secVersion = '1.0'; 
    public $to = ''; 
    public $Cc = array(); 
    public $Bcc = array(); 
    public $subject = ''; 
    public $message = ''; 
    public $attachment = array(); 
    public $embed = array(); 
    public $charset = 'UTF8'; 
    public $emailboundary = ''; 
    public $emailheader = ''; 
    public $textheader = '';
	public $wordwrap = 72;
    public $errors = array();

   	public function Mail() 
	{ 
        $this->emailboundary = uniqid(time()); 
    }

    public function setRecipients($toname, $toemail, $fromname, $fromemail) 
	{ 
        $this->to = "{$toname} <".$this->validateEmail($toemail).">"; 
        $email = $this->validateEmail($fromemail); 
        $this->emailheader .= "From: {$fromname} <{$email}>\r\n"; 
    }
     
    public function validateEmail($email) 
	{ 
        if (!preg_match('/^[A-Z0-9._%-]+@(?:[A-Z0-9-]+\\.)+[A-Z]{2,4}$/i', $email)) 
            die('The Email '.$email.' is not Valid.'); 
             
        return $email; 
    } 
     
    public function Cc($email) 
	{ 
        $this->Cc[] = $this->validateEmail($email); 
    } 
     
    function Bcc($email) 
	{ 
        $this->Bcc[] = $this->validateEmail($email); 
    } 
     
    public function buildHead($type) 
	{ 
        $count = count($this->$type); 
        if($count > 0) { 
            $this->emailheader .= "{$type}: "; 
            $array = $this->$type; 
            for($i=0; $i < $count; $i++) { 
                if($i > 0) $this->emailheader .= ','; 
                $this->emailheader .= $this->validateEmail($array[$i]); 
            } 
            $this->emailheader .= "\r\n"; 
        } 
    } 
     
    public function buildMimeHead() 
	{         
        $this->buildHead('Cc'); 
        $this->buildHead('Bcc'); 
         
        $this->emailheader .= "X-Mailer: simpleEmailClass v{$this->secVersion}\r\n"; 
        $this->emailheader .= "MIME-Version: 1.0\r\n"; 
    } 
     
    public function buildMessage($subject, $message = '') 
	{ 
        $textboundary = uniqid(time()); 
        $this->subject = strip_tags(trim($subject)); 
         
        $this->textheader = "Content-Type: multipart/alternative; boundary=\"$textboundary\"\r\n\r\n"; 
        $this->textheader .= "--{$textboundary}\r\n"; 
        $this->textheader .= "Content-Type: text/plain; charset=\"{$this->charset}\"\r\n"; 
        $this->textheader .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n"; 
        $this->textheader .= strip_tags($message)."\r\n\r\n"; 
        $this->textheader .= "--$textboundary\r\n"; 
        $this->textheader .= "Content-Type: text/plain; charset=\"$this->charset\"\r\n"; 
        $this->textheader .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n"; 
        $this->textheader .= "$message\r\n\r\n"; 
        $this->textheader .= "--{$textboundary}--\r\n\r\n"; 
    } 
     
    public function mime_type($file) 
	{ 
        return (function_exists('mime_content_type')) ? mime_content_type($file) : trim(exec('file -bi '.escapeshellarg($file))); 
    } 
     
    public function attachment($file) 
	{ 
        if(is_file($file)) { 
            $basename = basename($file); 
            $attachmentheader = "--{$this->emailboundary}\r\n"; 
            $attachmentheader .= "Content-Type: ".$this->mime_type($file)."; name=\"{$basename}\"\r\n"; 
            $attachmentheader .= "Content-Transfer-Encoding: base64\r\n"; 
            $attachmentheader .= "Content-Disposition: attachment; filename=\"{$basename}\"\r\n\r\n"; 
            $attachmentheader .= chunk_split(base64_encode(fread(fopen($file,"rb"),filesize($file))),72)."\r\n"; 
             
            $this->attachment[] = $attachmentheader; 
        } else { 
            die('The File '.$file.' does not exsist.'); 
        } 
    } 
     
    public function embed($file) 
	{ 
        if(is_file($file)) { 
            $basename = basename($file); 
            $fileinfo = pathinfo($basename); 
            $contentid = md5(uniqid(time())).".".$fileinfo['extension']; 
            $embedheader = "--{$this->emailboundary}\r\n"; 
            $embedheader .= "Content-Type: ".$this->mime_type($file)."; name=\"{$basename}\"\r\n"; 
            $embedheader .= "Content-Transfer-Encoding: base64\r\n"; 
            $embedheader .= "Content-Disposition: inline; filename=\"{$basename}\"\r\n"; 
            $embedheader .= "Content-ID: <{$contentid}>\r\n\r\n"; 
            $embedheader .= chunk_split(base64_encode(fread(fopen($file,"rb"),filesize($file))),72)."\r\n"; 
             
            $this->embed[] = $embedheader; 
                         
            return "<img src=3D\"cid:{$contentid}\">"; 
        } else { 
            die('The File '.$file.' does not exsist.'); 
        } 
    } 
     
    public function sendmail() 
	{ 
        $this->buildMimeHead(); 
         
        $header = $this->emailheader; 
         
        $attachcount = count($this->attachment); 
        $embedcount = count($this->embed); 
         
        if($attachcount > 0 || $embedcount > 0) { 
            $header .= "Content-Type: multipart/mixed; boundary=\"{$this->emailboundary}\"\r\n\r\n"; 
            $header .= "--{$this->emailboundary}\r\n"; 
            $header .= $this->textheader; 

            if($attachcount > 0) $header .= implode("",$this->attachment); 
            if($embedcount > 0) $header .= implode("",$this->embed); 
            $header .= "--{$this->emailboundary}--\r\n\r\n"; 
        } else { 
            $header .= $this->textheader; 
        } 
                 
        return mail($this->to, $this->subject, wordwrap($this->message, $this->wordwrap), $header); 
    } 
}


?>