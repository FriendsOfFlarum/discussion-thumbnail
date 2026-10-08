<?php

/*
 * This file is part of fof/discussion-thumbnail.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionThumbnail\Api;

use Flarum\Api\Context;
use Flarum\Api\Resource\EloquentBuffer;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Illuminate\Contracts\Cache\Repository;

class AddDiscussionThumbnailFields
{
    public function __construct(protected Repository $cache)
    {
    }

    public function __invoke(): array
    {
        return [
            Schema\Str::make('customThumbnail')
                ->nullable()
                ->get(function (Discussion $discussion, Context $context) {
                    // Batch the first posts through the relationship buffer:
                    // one query for the page, not one per discussion. Reading
                    // $discussion->firstPost here lazy-loaded each in turn.
                    EloquentBuffer::add($discussion, 'firstPost');

                    return function () use ($discussion, $context) {
                        if (!$discussion->relationLoaded('firstPost')) {
                            // The discussions resource's own relationship, not
                            // the request's primary resource: this field is
                            // also serialized on discussions included in other
                            // requests.
                            $resource = $context->api->getResource('discussions');

                            /** @var Schema\Relationship\ToOne|null $relationship */
                            $relationship = collect($context->fields($resource))->first(fn ($field) => $field->name === 'firstPost');

                            EloquentBuffer::load($discussion, 'firstPost', $relationship, $context);
                        }

                        return $this->thumbnail($discussion->getRelation('firstPost'));
                    };
                }),
        ];
    }

    protected function thumbnail(mixed $post): ?string
    {
        if (!($post instanceof CommentPost)) {
            return null;
        }

        $key = "fof:discussion-thumbnail:discussion:{$post->id}";
        $cached = $this->cache->get($key, false);

        $stale = is_array($cached) && $post->edited_at
            && ($cached['date'] === null || $post->edited_at->isAfter($cached['date']));

        if ($cached === false || $stale) {
            try {
                $content = $post->formatContent();
            } catch (\InvalidArgumentException $e) {
                $this->cache->forever($key, ['url' => null, 'date' => $post->edited_at]);

                return null;
            }

            if (!$content) {
                $this->cache->forever($key, ['url' => null, 'date' => null]);

                return null;
            }

            preg_match('/<img.+?src=[\"\'](.+?)[\"\'].*?>/i', $content, $match);

            $url = $match[1] ?? null;

            $this->cache->forever($key, ['url' => $url, 'date' => $post->edited_at]);

            return $url;
        }

        return $cached['url'] ?? null;
    }
}
