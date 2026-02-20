import IndexPage from 'flarum/forum/components/IndexPage';
import DiscussionPage from 'flarum/forum/components/DiscussionPage';
import { extend, override } from 'flarum/common/extend';
import app from 'flarum/forum/app';

export default function () {
  const isBlogTag = (tag, blogTags) => {
    if (!tag || !Array.isArray(blogTags) || blogTags.length === 0) {
      return false;
    }

    const tagId = parseInt(tag.id?.(), 10);
    const parentId = tag.parent?.() ? parseInt(tag.parent().id?.(), 10) : null;

    return blogTags.includes(tagId) || (parentId !== null && blogTags.includes(parentId));
  };

  // Redirect tag to blog category
  extend(IndexPage.prototype, 'oncreate', function () {
    const tag = typeof app.currentTag === 'function' ? app.currentTag() : null;
    const tagRedirectEnabled = app.forum.attribute('blogRedirectsEnabled') === 'both' || app.forum.attribute('blogRedirectsEnabled') === 'tags_only';

    // Only trigger when it's a tag page and the redirects are enabled
    if (tag && tagRedirectEnabled) {
      const blogTags = app.forum.attribute('blogTags') || [];

      // Tag is inside list
      if (isBlogTag(tag, blogTags)) {
        m.route.set(
          app.route('blogCategory', {
            slug: tag.slug(),
          })
        );
      }
    }
  });

  // Redirect discussion to blog article
  override(DiscussionPage.prototype, 'show', function (original, discussion) {
    const discussionRedirectEnabled =
      app.forum.attribute('blogRedirectsEnabled') === 'both' || app.forum.attribute('blogRedirectsEnabled') === 'discussions_only';

    if (discussionRedirectEnabled && discussion && discussion.tags().length > 0) {
      const blogTags = app.forum.attribute('blogTags') || [];

      const foundTags = discussion.tags().filter((tag) => {
        return isBlogTag(tag, blogTags);
      });

      // Only redirect if the discussion has blog tags
      if (foundTags.length > 0) {
        // Redirect to blog article
        const url = app.route('blogArticle', {
          id: discussion.slug(),
        });

        m.route.set(url, null, { replace: true });

        return null;
      }
    }

    return original(discussion);
  });
}
