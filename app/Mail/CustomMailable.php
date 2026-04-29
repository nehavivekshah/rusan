<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomMailable extends Mailable
{
    use Queueable, SerializesModels;

    // NO type hint here to match the parent Mailable class
    public $subject;
    public $viewName;
    public $viewData;
    public $trackingToken;
    // Add fromAddress/fromName if needed for build() logic
    protected ?string $fromAddress = null;
    protected ?string $fromName = null;


    /**
     * Create a new message instance.
     * Remove property promotion for subject/viewName.
     */
    public function __construct(
        string $subject,    // Type hint ok for parameter
        string $viewName,   // Type hint ok for parameter
        array $viewData = [],
        ?string $fromAddress = null,
        ?string $fromName = null,
        ?string $trackingToken = null
    ) {
        // Manually assign to properties
        $this->subject = $subject;
        $this->viewName = $viewName;
        $this->viewData = $viewData;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->trackingToken = $trackingToken;
    }

    /**
     * Build the message.
     * (This method is used if envelope() and content() are NOT defined)
     *
     * @return $this
     */
    public function build()
    {
        // Subject is set via the property which is automatically handled
        // by Laravel if the property exists, or you can call ->subject()
        // explicitly if desired, but usually not needed if property is set.

        $this->viewData['trackingToken'] = $this->trackingToken;

        $email = $this->view($this->viewName) // ->view() is still necessary
                      ->with($this->viewData);

        // You still need ->subject() if the property isn't automatically picked up
        // or if you prefer being explicit. Test this part.
        $email->subject($this->subject);


        // Set 'from' address IF it was provided
        if ($this->fromAddress) {
            $name = $this->fromName ?? config('mail.from.name');
            $email->from($this->fromAddress, $name);
        }
        // Otherwise, Laravel uses the default 'from' from config/middleware

        return $email;
    }

    /**
     * Render the mailable into HTML.
     * Overridden to inject link tracking logic.
     */
    public function render()
    {
        $html = parent::render();

        if ($this->trackingToken) {
            // Rewrite <a> tags to go through our tracking route
            $html = preg_replace_callback('/<a\s+[^>]*href="([^"]*)"/i', function($matches) {
                $originalUrl = $matches[1];
                
                // Skip mailto:, tel:, and already tracked links
                if (preg_match('/^(mailto:|tel:|#)/i', $originalUrl) || str_contains($originalUrl, '/t/c/')) {
                    return $matches[0];
                }

                $trackingUrl = route('email.track_click', [
                    'token' => $this->trackingToken,
                    'url' => $originalUrl
                ]);

                return str_replace($originalUrl, $trackingUrl, $matches[0]);
            }, $html);
        }

        return $html;
    }
}