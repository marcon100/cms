<?php
/**
 * Licensed under The GPL-3.0 License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since    2.0.0
 * @author   Christopher Castro <chris@quickapps.es>
 * @link     http://www.quickappscms.org
 * @license  http://opensource.org/licenses/gpl-3.0.html GPL-3.0 License
 */

/**
 * Default layout for rendering RSS feeds.
 *
 * This layout is used by `Node\Controller\ServeController::rss()`.
 * Or any other plugin which need to serve its content as RSS feed.
 *
 * @author Christopher Castro <chris@quickapps.es>
 */
?>
<?php
	if (!isset($channel)) {
		$channel = array();
	}

	if (!isset($channel['title'])) {
		$channel['title'] = $title_for_layout;
	}

	echo $this->Rss->document($this->Rss->channel([], $channel, $this->fetch('content')));
?>