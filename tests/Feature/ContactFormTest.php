<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Http\Livewire\ContactForm;
use App\Mail\ContactFormMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactFormTest extends TestCase
{
    
    public function main_page_contains_contact_form_livewire_component()
    {
        $this->get('/')
            ->assertSeeLivewire('contact-form');
    }

    
    public function contact_form_sends_out_an_email()
    {
        Mail::fake();

        Livewire::test(ContactForm::class)
            ->set('name', 'John')
            ->set('email', 'some@guy.com')
            ->set('phone', '12345')
            ->set('message', 'This is my message.')
            ->call('submitForm')
            ->assertSee('We received your message successfully and will get back to you shortly!');

        Mail::assertSent(function (ContactFormMailable $mail) {
            $mail->build();

            return $mail->hasTo('John@john.com') &&
                $mail->hasFrom('someguy@someguy.com') &&
                $mail->subject === 'Contact Form Submission';
        });
    }

    
    public function contact_form_name_field_is_required()
    {
        Livewire::test(ContactForm::class)
            ->set('email', 'some@guy.com')
            ->set('phone', '123456789')
            ->set('message', 'This is my message.')
            ->call('submitForm')
            ->assertHasErrors(['name' => 'required']);
    }

    
    public function contact_form_email_field_is_required()
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'John')
            ->set('phone', '123456789')
            ->set('message', 'This is my message.')
            ->call('submitForm')
            ->assertHasErrors(['email' => 'required']);
    }

    
    public function contact_form_email_field_fails_on_invalid_email()
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'John')
            ->set('email', 'notane@mail')
            ->set('phone', '12345')
            ->set('message', 'This is my message.')
            ->call('submitForm')
            ->assertHasErrors(['email' => 'email']);
    }

   
    public function contact_form_phone_field_is_required()
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'John')
            ->set('email', 'some@guy.com')
            ->set('message', 'This is my message.')
            ->call('submitForm')
            ->assertHasErrors(['phone' => 'required']);
    }

    
    public function contact_form_message_field_is_required()
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'John')
            ->set('email', 'someguy@someguy.com')
            ->set('phone', '123456789')
            ->call('submitForm')
            ->assertHasErrors(['message' => 'required']);
    }

    
    public function contact_form_message_field_has_minimum_characters()
    {
        Livewire::test(ContactForm::class)
            ->set('message', 'xyz')
            ->call('submitForm')
            ->assertHasErrors(['message' => 'min']);
    }
}