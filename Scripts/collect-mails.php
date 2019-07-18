<?php
// Script for collecting mails
// by seeseekey (https://seeseekey.net)
// licensed under MIT license

// Configuration
// Mailboxes to collect
$sourceMailboxes["test@example.org"]["server"] = "{mail.example.org/novalidate-cert}INBOX";
$sourceMailboxes["test@example.org"]["username"] = "user";
$sourceMailboxes["test@example.org"]["passwort"] = "secret";

$sourceMailboxes["spam@example.org"]["server"] = "{mail.example.org/novalidate-cert}INBOX";
$sourceMailboxes["spam@example.org"]["username"] = "user";
$sourceMailboxes["spam@example.org"]["passwort"] = "secret";

// Target mailbox

// $targetMailbox = "{pop3.example.com:110/pop3}INBOX";
// $targetMailbox = "{mail.example.org:110/imap/ssl}INBOX";
// $targetMailbox = "{mail.example.org:110/imap/ssl}";

$targetMailbox = "{mail.example.org/ssl/novalidate-cert}";
$targetUsername = "user";
$targetPassword = "secret";

// Open target mailbox
$mboxTarget = imap_open($targetMailbox, $targetUsername, $targetPassword) or die("Failed with error: " . imap_last_error());

// Open source mailboxes
while (list($key, $value) = each($sourceMailboxes)) {

    $mboxSource = imap_open($value["server"], $value["username"], $value["passwort"]) or die("Failed with error: " . imap_last_error());
    $mailboxInformation = imap_check($mboxSource);
    $overviewSourceMailBox = imap_fetch_overview($mboxSource, "1:{$mailboxInformation->Nmsgs}", 0);

    // Create folder on target mailbox
    imap_createmailbox($mboxTarget, imap_utf7_encode("$targetMailbox$key"));

    foreach ($overviewSourceMailBox as $overview) {
        $message = imap_fetchheader($mboxSource, $overview->msgno) . imap_body($mboxSource, $overview->msgno);

        // Store mail into target mailbox
        if (!imap_append($mboxTarget, mb_convert_encoding("$targetMailbox$key" . "" . "", "UTF7-IMAP", "ISO-8859-1"), $message, "")) {
            die("Error: " . imap_last_error());
        }

        // Mark mail from source mailbox as deleted
        imap_delete($mboxSource, $overview->msgno);
    }

    // Delete all marked mails and close connection to source mailbox
    imap_expunge($mboxSource);
    imap_close($mboxSource);
}

// Close connection to target mailbox
imap_close($mboxTarget);