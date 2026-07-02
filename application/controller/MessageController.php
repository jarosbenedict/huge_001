<?php

class MessageController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // nur für eingelogte user 
        Auth::checkAuthentication();
    }

    //alle parnter anzeigen mit letztem message und ungelesen anzahl
    public function index()
    {
        $this->View->render('message/index', array(
            'partners' => MessageModel::getConversationPartners(),
            'unread'   => MessageModel::countUnread()
        ));
    }

    // conversion mit partner anzeigen, ungelesene als gelesen markieren
    public function conversation($partner_id)
    {
        $partner_id = (int) $partner_id;

        // Partnerdaten holen, um Namen anzuzeigen
        $partner = MessageModel::getUserById($partner_id);

        if (!$partner) {
            Session::add('feedback_negative', 'User nt found.');
            Redirect::to('message');
            return;
        }

        // Nachrichten als gelesen markieren
        MessageModel::markAsRead($partner_id);

        $this->View->render('message/conversation', array(
            'partner'    => $partner,
            'messages'   => MessageModel::getMessages($partner_id),
            'partner_id' => $partner_id
        ));
    }

    // Nachricht senden
    public function send($receiver_id, $message_text = null)
    {
        $receiver_id = (int) $receiver_id;

        // post zuerst aber mit fallback auf url
        $text = Request::post('message_text');
        if (empty($text) && !empty($message_text)) {
            $text = urldecode($message_text);
        }

        MessageModel::sendMessage($receiver_id, $text);

        // ---------------------------------------------------------------------
        // KI-Bot Integration: Wenn Empfänger der Bot-User ist,
        // rufe Gemini API auf und speichere die Antwort als neue Nachricht.
        // ---------------------------------------------------------------------
        $botUserId = (int) Config::get('BOT_USER_ID');
        if ($receiver_id === $botUserId && $botUserId > 0) {
            $aiResponse = GeminiApiService::ask($text);

            // Bot-Antwort als Nachricht speichern (Bot sendet an aktuellen User)
            MessageModel::sendMessageFromBot(
                Session::get('user_id'),   // Empfänger = aktueller User
                $botUserId,                // Sender = Bot
                $aiResponse
            );
        }

        Redirect::to('message/conversation/' . $receiver_id);
    }
}
