# Discussion Thumbnail by FriendsOfFlarum

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/fof/discussion-thumbnail.svg)](https://packagist.org/packages/fof/discussion-thumbnail) [![OpenCollective](https://img.shields.io/badge/opencollective-fof-blue.svg)](https://opencollective.com/fof/donate)

A [Flarum](http://flarum.org) extension. Replaces the author avatar in the discussion list with the first image found in the discussion's opening post.

## How it works

When the discussion list is rendered, each discussion's author avatar is replaced with a thumbnail of the first `<img>` found in the HTML of the first post. If the first post contains no image, the normal user avatar is shown instead.

**Backend:** `AddDiscussionThumbnail` is registered as an API serializer attribute on `BasicDiscussionSerializer`. For each discussion it:

1. Loads the first post and calls `formatContent()` to render the full HTML
2. Extracts the `src` of the first `<img>` tag via regex
3. Caches the result forever against the post ID, keyed as `fof:discussion-thumbnail:discussion:{id}`
4. Returns the URL as the `customThumbnail` attribute on the discussion

The cache is invalidated when the post's `edited_at` timestamp is newer than the cached date, so editing the first post to change its image is reflected on next load.

**Frontend:** The `customThumbnail` attribute is read in a `contentItems` extension on `DiscussionListItem`. When a thumbnail URL is present, the `authorAvatar` item is replaced with a `DiscussionThumbnail` component wrapped in the same `Tooltip` + `Link` structure as the normal avatar. The thumbnail is styled with the `.Avatar` class so it matches core avatar sizing and alignment at all breakpoints.

## Settings

| Setting | Default | Description |
|---|---|---|
| Link to discussion | `false` | When enabled, clicking the thumbnail navigates to the discussion instead of the author's profile |

## Installation

```sh
composer require fof/discussion-thumbnail:"*"
```

Enable the extension in your Flarum admin panel.

## Updating

```sh
composer update fof/discussion-thumbnail
php flarum cache:clear
```

## Links

[![OpenCollective](https://img.shields.io/badge/donate-friendsofflarum-44AEE5?style=for-the-badge&logo=open-collective)](https://opencollective.com/fof/donate)

- [Packagist](https://packagist.org/packages/fof/discussion-thumbnail)
- [GitHub](https://github.com/FriendsOfFlarum/discussion-thumbnail)
- [Discuss](https://discuss.flarum.org/d/22231)

An extension by [FriendsOfFlarum](https://github.com/FriendsOfFlarum).
