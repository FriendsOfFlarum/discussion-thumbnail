import Component, { ComponentAttrs } from "flarum/common/Component";
import type Mithril from "mithril";

interface DiscussionThumbnailAttrs extends ComponentAttrs {
  src: string;
}

export const failedSet = new Set<string>();

export default class DiscussionThumbnail extends Component<DiscussionThumbnailAttrs> {
  view(): Mithril.Children {
    return (
      <img
        className="DiscussionListItem-thumbnail"
        src={this.attrs.src}
        onerror={this.onError.bind(this)}
      />
    );
  }

  onError(): void {
    failedSet.add(this.attrs.src);
    m.redraw();
  }
}
