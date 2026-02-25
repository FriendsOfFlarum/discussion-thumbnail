import app from "flarum/forum/app";
import extendDiscussionListItem from "./extenders/extendDiscussionListItem";

export { default as extend } from "./extend";

app.initializers.add("fof-discussion-thumbnail", () => {
  extendDiscussionListItem();
});
