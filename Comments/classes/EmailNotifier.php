<?php

/**
 * EmailNotifier.php
 * Handles sending email notifications for new comment events.
 */
class EmailNotifier {
  /** @var string The destination email address for notifications. */
  private const TO = 'email@email.com';

  /** @var string The sender email address for notifications. */
  private const FROM = 'email@email.com';

  /**
   * Sends an email notification to the administrator when a new comment is posted.
   *
   * @param string $pageKey The unique identifier for the page being commented on.
   * @param string $author The name of the person who posted the comment.
   * @param string $body The content of the comment.
   * @param int $commentId The unique ID of the comment in the database.
   * @return void
   */
  public function notify(string $pageKey, string $author, string $body, int $commentId): void {
    $subject = "New comment on {$pageKey}";
    $message = "Page: {$pageKey}\nAuthor: {$author}\nComment ID: {$commentId}\nComment: {$body}";
    
    @mail(
      self::TO,
      $subject,
      $message,
      "From: " . self::FROM . "\r\nContent-Type: text/plain; charset=UTF-8"
    );
  }
}