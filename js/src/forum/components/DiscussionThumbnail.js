import Component from 'flarum/Component';
import LoadingIndicator from 'flarum/components/LoadingIndicator';

import 'object.assign/auto';
import 'intersection-observer';

const loadedSet = new Set();

export default class DiscussionThumbnail extends Component {
    view() {
        const attrs = { ...this.props.attrs };
        const loaded = loadedSet.has(this.props.src);

        if (!attrs.style) attrs.style = {};

        attrs.style.position = 'relative';

        if (!loaded) {
            attrs.style.opacity = '0';

            attrs.config = this.configImage.bind(this);
            attrs.onload = this.onLoad.bind(this);
        }

        attrs[loaded ? 'src' : 'data-src'] = this.props.src;

        return (
            <div className={attrs.className}>
                {!loaded && <LoadingIndicator />}
                <img {...attrs} />
            </div>
        );
    }

    configImage(el, isInitialized) {
        if (isInitialized) return;

        const observer = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    observer.unobserve(lazyImage);
                }
            });
        });

        observer.observe(el);
    }

    onLoad(evt) {
        const img = evt.target;

        loadedSet.add(this.props.src);

        img.style.opacity = 1;

        this.$('.LoadingIndicator').remove();
    }
}
