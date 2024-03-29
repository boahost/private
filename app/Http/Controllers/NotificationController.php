<?php

namespace App\Http\Controllers;

use \Notification;

use App\Models\Contact;
use App\Notifications\CustomerNotification;
use App\Notifications\SupplierNotification;
use App\NotificationTemplate;
use App\Restaurant\Booking;

use App\Models\Transaction;
use App\Utils\NotificationUtil;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param NotificationUtil $notificationUtil
     * @return void
     */
    public function __construct(NotificationUtil $notificationUtil)
    {
        $this->notificationUtil = $notificationUtil;
    }

    /**
     * Display a notification view.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTemplate($id, $template_for)
    {
        $business_id = request()->session()->get('user.business_id');

        $notification_template = NotificationTemplate::getTemplate($business_id, $template_for);

        $contact = null;
        $transaction = null;
        if ($template_for == 'new_booking') {
            $transaction = Booking::where('business_id', $business_id)
                            ->with(['customer'])
                            ->find($id);

            $contact = $transaction->customer;
        } elseif ($template_for == 'send_ledger') {
            $contact = Contact::find($id);
        } else {
            $transaction = Transaction::where('business_id', $business_id)
                            ->with(['contact'])
                            ->find($id);

            $contact = $transaction->contact;
        }

        $customer_notifications = NotificationTemplate::customerNotifications();
        $supplier_notifications = NotificationTemplate::supplierNotifications();
        $general_notifications = NotificationTemplate::generalNotifications();

        $template_name = '';

        $tags = [];
        if (array_key_exists($template_for, $customer_notifications)) {
            $template_name = $customer_notifications[$template_for]['name'];
            $tags = $customer_notifications[$template_for]['extra_tags'];
        } elseif (array_key_exists($template_for, $supplier_notifications)) {
            $template_name = $supplier_notifications[$template_for]['name'];
            $tags = $supplier_notifications[$template_for]['extra_tags'];
        } elseif (array_key_exists($template_for, $general_notifications)) {
            $template_name = $general_notifications[$template_for]['name'];
            $tags = $general_notifications[$template_for]['extra_tags'];
        }

        //for send_ledger notification template
        $start_date = request()->input('start_date');
        $end_date = request()->input('end_date');

        return view('notification.show_template')
                ->with(compact('notification_template', 'transaction', 'tags', 'template_name', 'contact', 'start_date', 'end_date'));
    }

    /**
     * Sends notifications to customer and supplier
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request)
    {
        // if (!auth()->user()->can('send_notification')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $notAllowed = $this->notificationUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $customer_notifications = NotificationTemplate::customerNotifications();
            $supplier_notifications = NotificationTemplate::supplierNotifications();

            $data = $request->only(['to_email', 'subject', 'email_body', 'mobile_number', 'sms_body', 'notification_type', 'cc', 'bcc']);

            $emails_array = array_map('trim', explode(',', $data['to_email']));

            $transaction_id = $request->input('transaction_id');
            $business_id = request()->session()->get('business.id');

            $orig_data = [
                'email_body' => $data['email_body'],
                'sms_body' => $data['sms_body'],
                'subject' => $data['subject']
            ];

            if ($request->input('template_for') == 'new_booking') {
                $tag_replaced_data = $this->notificationUtil->replaceBookingTags($business_id, $orig_data, $transaction_id);

                $data['email_body'] = $tag_replaced_data['email_body'];
                $data['sms_body'] = $tag_replaced_data['sms_body'];
                $data['subject'] = $tag_replaced_data['subject'];
            } else {
                $tag_replaced_data = $this->notificationUtil->replaceTags($business_id, $orig_data, $transaction_id);

                $data['email_body'] = $tag_replaced_data['email_body'];
                $data['sms_body'] = $tag_replaced_data['sms_body'];
                $data['subject'] = $tag_replaced_data['subject'];
            }
            

            $data['email_settings'] = request()->session()->get('business.email_settings');

            $data['sms_settings'] = request()->session()->get('business.sms_settings');

            $notification_type = $request->input('notification_type');

            if (array_key_exists($request->input('template_for'), $customer_notifications)) {
                if ($notification_type == 'email_only') {
                    Notification::route('mail', $emails_array)
                                    ->notify(new CustomerNotification($data));
                } elseif ($notification_type == 'sms_only') {
                    $this->notificationUtil->sendSms($data);
                } elseif ($notification_type == 'both') {
                    Notification::route('mail', $emails_array)
                                ->notify(new CustomerNotification($data));

                    $this->notificationUtil->sendSms($data);
                }
            } elseif (array_key_exists($request->input('template_for'), $supplier_notifications)) {
                if ($notification_type == 'email_only') {
                    Notification::route('mail', $emails_array)
                                    ->notify(new SupplierNotification($data));
                } elseif ($notification_type == 'sms_only') {
                    $this->notificationUtil->sendSms($data);
                } elseif ($notification_type == 'both') {
                    Notification::route('mail', $emails_array)
                                ->notify(new SupplierNotification($data));

                    $this->notificationUtil->sendSms($data);
                }
            }

            $output = ['success' => 1, 'msg' => __('lang_v1.notification_sent_successfully')];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }
}
