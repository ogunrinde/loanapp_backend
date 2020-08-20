<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Activitymail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */


    public $data;


    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->data['subject'] == 'Account Created')
        {
            return $this->subject($this->data['subject'])->view('Mail.mail');
        }
        else if($this->data['subject'] == 'Loan Request')
        {
            return $this->subject($this->data['subject'])->view('Mail.loanrequest');
        }
        else if($this->data['subject'] == 'Sure Deals')
        {
            return $this->subject($this->data['subject'])->view('Mail.suredeal');
        }
        else if($this->data['subject'] == 'Loan Repayment')
        {
            return $this->subject($this->data['subject'])->view('Mail.loanrepayment');
        }
        else if($this->data['subject'] == 'Verify Account')
        {
            return $this->subject($this->data['subject'])->view('Mail.verifyemail');
        } 

        else if($this->data['subject'] == 'Loan Approved')
        {
            return $this->subject($this->data['subject'])->view('Mail.loanstatus');
        }    
        else if($this->data['subject'] == 'Loan Connect')
        {
            return $this->subject($this->data['subject'])->view('Mail.loanstatus');
        }
        else if($this->data['subject'] == 'Loan Offer Accepted')
        {
            return $this->subject($this->data['subject'])->view('Mail.loanstatus');
        }
        else if($this->data['subject'] == 'Loan Offer Declined')
        {
            return $this->subject($this->data['subject'])->view('Mail.loanstatus');
        }
    }
}

?>