<?php
// Script for collecting mails
// by seeseekey (https://seeseekey.net)
// licensed under MIT license

// Configuration
// Mailboxes to collect
// Source mailboxes
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
$targetMailboxUsername = "user";
$targetMailboxPassword = "secret";

// Operate
$mboxTarget = imap_open($targetMailboxServer, $targetMailboxUsername, $targetMailboxPassword) or die ("Failed with error: " . imap_last_error());

// Open source mailboxes
foreach ($sourceMailboxes as $key => $value) {

    // Open source mailbox
    $mboxSource = imap_open($value["server"], $value["username"], $value["passwort"]) or die ("Failed with error: " . imap_last_error());
    $mboxSourceInformation = imap_check($mboxSource);

    if ($mboxSourceInformation->Nmsgs == 0) {
        imap_close($mboxSource);
        continue;
    }

    $mboxSourceOverview = imap_fetch_overview($mboxSource, "1:{$mboxSourceInformation->Nmsgs}", 0);

    // Create target folder, if needed
    $mboxTargetMailboxes = imap_list($mboxTarget, $targetMailboxServer, "*");

    if (!in_array("$targetMailboxServer$key", $mboxTargetMailboxes)) {
        imap_createmailbox($mboxTarget, imap_utf7_encode("$targetMailboxServer$key"));
    }

    foreach ($mboxSourceOverview as $overview) {

        $message = imap_fetchheader($mboxSource, $overview->msgno) . imap_body($mboxSource, $overview->msgno);

        if (!imap_append($mboxTarget, mb_convert_encoding("$targetMailboxServer$key", "UTF7-IMAP", "ISO-8859-1"), $message, "")) {
            die ("Error: " . imap_last_error());
        }

        // Mark mail from source mailbox as deleted
        imap_delete($mboxSource, $overview->msgno);
    }

    // Delete all marked mails and close connection to source mailbox
    imap_expunge($mboxSource);
    imap_close($mboxSource);
}

imap_close($mboxTarget);
?>
