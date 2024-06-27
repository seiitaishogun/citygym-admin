<?php
/**
 * @author tmtuan
 * created Date: 04-Mar-21
 */
namespace App\Providers;

use App\Models\Settings;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Mail\MailServiceProvider;


class CustomMailServiceProvider extends MailServiceProvider {
//    protected function registerSwiftTransport(){
//        $this->app['swift.transport'] = $this->app->share(function($app)
//        {
//            return new CustomTransportManager($app);
//        });
//    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerIlluminateMailer();
    }

    /**
     * Register the Illuminate mailer instance.
     *
     * @return void
     */
    protected function registerIlluminateMailer()
    {
        $this->app->singleton('mail.manager', function ($app) {
            return new MailManager($app);
        });

        $this->app->bind('mailer', function ($app) {
            return $app->make('mail.manager')->mailer();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $settings = Settings::where('group', 'system_cf')
            ->where('item', 'email_settings')
            ->get()->first();

        if (!empty($settings)) {
            $emailServices = json_decode($settings->value);
            $config = array(
                'driver'     => 'smtp',
                'host'       => $emailServices->host,
                'port'       => $emailServices->port,
                'username'   => $emailServices->username,
                'password'   => $emailServices->password,
                'encryption' => $emailServices->encryption,
                'timeout' => null,
                'auth_mode' => null,
                'sendmail'   => '/usr/sbin/sendmail -bs',
                'pretend'    => false,
            );

            Config::set('mail', $config);

        }
    }
}
