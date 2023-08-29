<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Retrives notification template from database
     *
     * @param  int  $business_id
     * @param  string  $template_for
     * @return array $template
     */
    public static function getTemplate($business_id, $template_for)
    {
        $notif_template = NotificationTemplate::where('business_id', $business_id)
        ->where('template_for', $template_for)
        ->first();
        $template = [
            'subject' => !empty($notif_template->subject) ? $notif_template->subject : '',
            'sms_body' => !empty($notif_template->sms_body) ? $notif_template->sms_body : '',
            'email_body' => !empty($notif_template->email_body) ? $notif_template->email_body
            : '',
            'template_for' => $template_for,
            'cc' => !empty($notif_template->cc) ? $notif_template->cc : '',
            'bcc' => !empty($notif_template->bcc) ? $notif_template->bcc : '',
            'auto_send' => !empty($notif_template->auto_send) ? 1
            : 0,
            'auto_send_sms' => !empty($notif_template->auto_send_sms) ? 1
            : 0
        ];

        return $template;
    }

    public static function customerNotifications()
    {
        return [
            'new_sale' => [
                'name' => __('lang_v1.new_sale'),
                'extra_tags' => ['{business_name}', '{business_logo}', '{contact_name}', '{invoice_number}', '{invoice_url}', '{total_amount}', '{paid_amount}', '{due_amount}', '{cumulative_due_amount}', '{due_date}']
            ],
            'payment_received' => [
                'name' => __('lang_v1.payment_received'),
                'extra_tags' => ['{business_name}', '{business_logo}', '{contact_name}', '{invoice_number}', '{payment_ref_number}', '{received_amount}']
            ],
            'payment_reminder' => [
                'name' =>  __('lang_v1.payment_reminder'),
                'extra_tags' => ['{business_name}', '{business_logo}', '{contact_name}', '{invoice_number}', '{due_amount}', '{cumulative_due_amount}', '{due_date}']
            ],
            'new_booking' => [
                'name' => __('lang_v1.new_booking'),
                'extra_tags' => self::bookingNotificationTags()
            ],
        ];
    }

    public static function generalNotifications()
    {
        return [
            'send_ledger' => [
                'name' => __('lang_v1.send_ledger'),
                'extra_tags' => ['{business_name}', '{business_logo}', '{contact_name}', '{balance_due}']
            ],
        ];
    }

    public static function supplierNotifications()
    {
        return [
            'new_order' => [
                'name' => __('lang_v1.new_order'),
                'extra_tags' => ['{business_name}', '{business_logo}', '{contact_business_name}', '{contact_name}', '{order_ref_number}', '{total_amount}', '{received_amount}', '{due_amount}']
            ],
            'payment_paid' => [
                'name' => __('lang_v1.payment_paid'),
                'extra_tags' => ['{business_name}', '{business_logo}', '{contact_business_name}', '{contact_name}', '{order_ref_number}', '{payment_ref_number}', '{paid_amount}']
            ],
            'items_received' => [
                'name' =>  __('lang_v1.items_received'), 
                'extra_tags' => ['{business_name}', '{business_logo}', '{contact_business_name}', '{contact_name}', '{order_ref_number}'],
            ],
            'items_pending' => [
                'name' => __('lang_v1.items_pending'),
                'extra_tags' => ['{business_name}', '{business_logo}', '{contact_business_name}', '{contact_name}', '{order_ref_number}']
            ],
        ];
    }

    public static function notificationTags()
    {
        return ['{contact_name}', '{invoice_number}', '{total_amount}',
        '{paid_amount}', '{due_amount}', '{business_name}', '{business_logo}', '{cumulative_due_amount}', '{due_date}', '{contact_business_name}'];
    }

    public static function bookingNotificationTags()
    {
        return ['{contact_name}', '{table}', '{start_time}',
        '{end_time}', '{location}', '{service_staff}', '{correspondent}', '{business_name}', '{business_logo}'];
    }

    public static function defaultNotificationTemplates($business_id = null)
    {
        $notification_template_data = [
            [
                'business_id' => $business_id,
                'template_for' => 'new_sale',
                'email_body' => '<p>Dear {contact_name},</p>

                <p>Número da fatura {invoice_number}<br />
                Total : {total_amount}<br /></p>

                <p>Obrigado por comprar conosco.</p>

                <p>{business_logo}</p>

                <p>&nbsp;</p>',
                'sms_body' => 'Querido {contact_name}, Obrigado por comprar conosco. {business_name}',
                'subject' => 'Obrigado de{business_name}',
                'auto_send' => '0'
            ],

            [
                'business_id' => $business_id,
                'template_for' => 'payment_received',
                'email_body' => '<p>Querido {contact_name},</p>

                <p>Recebemos um pagamento de {received_amount}</p>

                <p>{business_logo}</p>',
                'sms_body' => 'Qurido {contact_name}, Recebemos um pagamento de {received_amount}. {business_name}',
                'subject' => 'Pagamento recebido, de {business_name}',
                'auto_send' => '0'
            ],
            [
                'business_id' => $business_id,
                'template_for' => 'payment_reminder',
                'email_body' => '<p>Qurido {contact_name},</p>

                <p>
                Isto é para lembrá-lo de que você tem um pagamento pendente de {due_amount}. Por favor, pague o mais rápido possível.</p>

                <p>{business_logo}</p>',
                'sms_body' => 'Querido {contact_name}, Você tem o pagamento pendente de {due_amount}. Por favor, pague o mais rápido possível. {business_name}',
                'subject' => 'Lembrete de pagamento, de {business_name}',
                'auto_send' => '0'
            ],
            [
                'business_id' => $business_id,
                'template_for' => 'new_booking',
                'email_body' => '<p>Qurido {contact_name},</p>

                <p>Sua reserva está confirmada</p>

                <p>Data: {start_time} até {end_time}</p>

                <p>Mesa: {table}</p>

                <p>Localização: {location}</p>

                <p>{business_logo}</p>',
                'sms_body' => 'Querido {contact_name}, Sua reserva está confirmada. Data: {start_time} até {end_time}, Mesa: {table}, Localização: {location}','subject' => 'Reserva confirmada - {business_name}',
                'auto_send' => '0'
            ],
            [
                'business_id' => $business_id,
                'template_for' => 'new_order',
                'email_body' => '<p>Querido {contact_name},</p>

                <p>Temos um novo pedido com o número de referência {order_ref_number}. Por favor, processe os produtos o mais rápido possível.</p>

                <p>{business_name}<br />
                {business_logo}</p>',
                'sms_body' => 'Querido {contact_name}, Temos um novo pedido com número de referência {order_ref_number}. Por favor, processe os produtos o mais rápido possível. {business_name}',
                'subject' => 'Nova Ordem, de {business_name}',
                'auto_send' => '0'
            ],
            [
                'business_id' => $business_id,
                'template_for' => 'payment_paid',
                'email_body' => '<p>Querido {contact_name},</p>

                <p>Pagamos o valor {paid_amount} novamente número da fatura {order_ref_number}.<br />
                Por favor, anote.</p>

                <p>{business_name}<br />
                {business_logo}</p>',
                'sms_body' => 'Pagamos o valor {paid_amount} novamente número da fatura {order_ref_number}.
                Por favor, anote. {business_name}',
                'subject' => 'Pagamento Pago, de{business_name}',
                'auto_send' => '0'
            ],
            [
                'business_id' => $business_id,
                'template_for' => 'items_received',
                'email_body' => '<p>Querido {contact_name},</p>

                <p>Recebemos todos os itens do número de referência da fatura {order_ref_number}. Obrigado por processá-lo.</p>

                <p>{business_name}<br />
                {business_logo}</p>',
                'sms_body' => 'Recebemos todos os itens do número de referência da fatura {order_ref_number}. Obrigado por processá-lo. {business_name}',
                'subject' => 'Obrigado por processá-lo. {business_name}',
                'auto_send' => '0'
            ],
            [
                'business_id' => $business_id,
                'template_for' => 'items_pending',
                'email_body' => '<p>Querido {contact_name},<br />
               Isto é para lembrá-lo de que ainda não recebemos alguns itens do número de referência da fatura {order_ref_number}. Por favor, processe-o o mais rápido possível.</p>

                <p>{business_name}<br />
                {business_logo}</p>',
                'sms_body' => 'Isto é para lembrá-lo de que ainda não recebemos alguns itens do número de referência da fatura {order_ref_number}. Por favor, processe-o o mais rápido possível.{business_name}',
                'subject' => 'Itens Pendentes, de {business_name}',
                'auto_send' => '0'
            ]
        ];

        return $notification_template_data;
    }
}
