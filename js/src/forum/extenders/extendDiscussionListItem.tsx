import app from "flarum/forum/app";
import { extend } from "flarum/common/extend";
import DiscussionListItem from "flarum/forum/components/DiscussionListItem";
import Tooltip from "flarum/common/components/Tooltip";
import Link from "flarum/common/components/Link";
import humanTime from "flarum/common/utils/humanTime";

import DiscussionThumbnail from "../components/DiscussionThumbnail";

export default function extendDiscussionListItem() {
  extend(DiscussionListItem.prototype, "contentItems", function (items) {
    const image = this.attrs.discussion.customThumbnail();

    if (!image) return;

    const user = this.attrs.discussion.user();
    const href = app.forum.attribute("fof-discussion-thumbnail.link_to_discussion")
      ? app.route.discussion(this.attrs.discussion)
      : user
        ? app.route.user(user)
        : "#";

    items.setContent(
      "authorAvatar",
      <Tooltip
        text={app.translator.trans("core.forum.discussion_list.started_text", {
          user,
          ago: humanTime(this.attrs.discussion.createdAt()),
        })}
        position="right"
      >
        <Link className="DiscussionListItem-author" href={href}>
          <DiscussionThumbnail src={image} />
        </Link>
      </Tooltip>,
    );
  });
}
