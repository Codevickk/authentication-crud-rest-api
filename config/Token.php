<?php

class Token
{
    private $secret_key = "YOUR_SECRET_KEY";
    private $issuer_claim = "CONTACT_MANAGER";
    private $audience_claim = "THE_AUDIENCE";
    private $issuedat_claim = null;
    private $notbefore_claim = null;
    private $expire_claim = null;


    public function secret_key()
    {
        return $this->secret_key;
    }

    public function issuer_claim()
    {
        return $this->issuer_claim;
    }

    public function audience_claim()
    {
        return $this->audience_claim;
    }

    public function issuedat_claim()
    {
        $this->issuedat_claim = time();
        return $this->issuedat_claim;
    }

    public function notbefore_claim()
    {
        $this->notbefore_claim = $this->issuedat_claim;
        return $this->notbefore_claim;
    }

    public function expire_claim()
    {
        $this->expire_claim = $this->issuedat_claim + 100000000000000;
        return $this->expire_claim;
    }
}
