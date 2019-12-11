<?php

/*
 * This file is part of fof/discussion-thumbnail.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionThumbnail\Listener;

use Flarum\Api\Event\Serializing;
use Flarum\Api\Serializer\BasicDiscussionSerializer;
use Flarum\Post\CommentPost;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;

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

    public function handle(Serializing $event)
    {
        if ($event->isSerializer(BasicDiscussionSerializer::class)) {
            $post = $event->model->firstPost;

            if (!($post instanceof CommentPost)) {
                return;
            }

            $key = "fof-discussion-thumbnail.discussion.{$post->id}";
            $cached = $this->cache->get($key);
            $thumbnail = Arr::get($cached, 'url');

            if (!$this->cache->has($key) || ($post->edited_at && Arr::has($cached, 'date') && $post->edited_at->isAfter($cached['date']))) {
                $content = $event->model->firstPost->formatContent();

                if (!$content) {
                    return;
                }

                preg_match('/<img.+?src=[\"\'](.+?)[\"\'].*?>/i', $content, $match);

                $thumbnail = @$match[1];

                $this->cache->forever($key, $match ? [
                    'url'  => @$match[1],
                    'date' => $post->edited_at,
                ] : null);
            }

            $event->attributes['customThumbnail'] = $thumbnail;
        }
    }
}
