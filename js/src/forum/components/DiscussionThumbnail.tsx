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
        oncreate={this.attachErrorHandler.bind(this)}
        onupdate={this.attachErrorHandler.bind(this)}
      />
    );
  }

  attachErrorHandler(vnode: Mithril.VnodeDOM): void {
    const img = vnode.dom as HTMLImageElement;

    img.onerror = () => {
      failedSet.add(this.attrs.src);
      m.redraw();
    };

    // If the image already errored before oncreate fired (e.g. instant 404),
    // naturalWidth === 0 and complete === true indicates a broken image.
    if (img.complete && img.naturalWidth === 0) {
      failedSet.add(this.attrs.src);
      m.redraw();
    }
  }
}
