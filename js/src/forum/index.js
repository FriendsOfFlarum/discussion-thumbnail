import get from 'lodash/get';

import { extend } from 'flarum/common/extend';
import Model from 'flarum/common/Model';
import Discussion from 'flarum/common/models/Discussion';
import DiscussionListItem from 'flarum/forum/components/DiscussionListItem';
import Tooltip from 'flarum/common/components/Tooltip';

import DiscussionThumbnail from './components/DiscussionThumbnail';

const find = (obj, clazz) => obj && obj.children && obj.children.filter((e) => get(e, 'attrs.className', '').indexOf(clazz) !== -1)[0];

app.initializers.add('fof/discussion-thumbnail', () => {
    Discussion.prototype.customThumbnail = Model.attribute('customThumbnail');

    extend(DiscussionListItem.prototype, 'view', function (vdom) {
        const image = this.attrs.discussion.customThumbnail();

        if (!image) return;

        const content = find(vdom, 'DiscussionListItem-content');

        if (!content || !content.children) return;

        const tooltip = content.children.find((e) => e?.tag === Tooltip);
        const author = find(tooltip, 'DiscussionListItem-author');
        const avatar = find(author, 'Avatar');

        if (!avatar) return;

        delete avatar.attrs.src;

        author.children[author.children.indexOf(avatar)] = <DiscussionThumbnail elementAttrs={avatar.attrs} src={image} />;
    });
});
