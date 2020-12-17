<?php

/*
 * This file is part of fof/discussion-thumbnail.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionThumbnail;

use Flarum\Api\Serializer\BasicDiscussionSerializer;
use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\ApiSerializer(BasicDiscussionSerializer::class))
        ->mutate(Listener\AddDiscussionThumbnail::class),
];
