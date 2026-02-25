import app from "flarum/admin/app";
import Extend from "flarum/common/extenders";

export default [
  new Extend.Admin() //
    .setting(() => ({
      label: app.translator.trans(
        "fof-discussion-thumbnail.admin.settings.link_to_discussion_label",
      ),
      setting: "fof-discussion-thumbnail.link_to_discussion",
      type: "boolean",
    })),
];
