import Component, { ComponentAttrs } from "flarum/common/Component";
import type Mithril from "mithril";

interface DiscussionThumbnailAttrs extends ComponentAttrs {
  src: string;
}

export default class DiscussionThumbnail extends Component<DiscussionThumbnailAttrs> {
  view(): Mithril.Children {
    return <img className="DiscussionListItem-thumbnail" src={this.attrs.src} />;
  }
}
