<?php

/*
 * This file is part of fof/discussion-thumbnail.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionThumbnail\Listener;

use Flarum\Api\Serializer\BasicDiscussionSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Illuminate\Contracts\Cache\Repository;

class AddDiscussionThumbnail
{
    /**
     * @var Repository
     */
    protected $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function __invoke(BasicDiscussionSerializer $serializer, Discussion $discussion): array
    {
        $post = $discussion->firstPost;

        if (!($post instanceof CommentPost)) {
            return [];
        }

        $key = "fof:discussion-thumbnail:discussion:{$post->id}";
        $cached = $this->cache->get($key, false);

        $stale = is_array($cached) && $post->edited_at
            && ($cached['date'] === null || $post->edited_at->isAfter($cached['date']));

        if ($cached === false || $stale) {
            try {
                $content = $post->formatContent();
            } catch (\InvalidArgumentException $e) {
                // Post content is not valid s9e XML (e.g. plain-text legacy content).
                // Cache as no-image so we don't re-attempt on every request.
                $this->cache->forever($key, ['url' => null, 'date' => $post->edited_at]);

                return [];
            }

            if (!$content) {
                $this->cache->forever($key, ['url' => null, 'date' => null]);

                return [];
            }

            preg_match('/<img.+?src=[\"\'](.+?)[\"\'].*?>/i', $content, $match);

            $url = $match[1] ?? null;

            $this->cache->forever($key, ['url' => $url, 'date' => $post->edited_at]);

            return ['customThumbnail' => $url];
        }

        return ['customThumbnail' => $cached['url'] ?? null];
    }
}
