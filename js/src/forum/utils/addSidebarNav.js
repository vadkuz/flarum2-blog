import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import LinkButton from 'flarum/common/components/LinkButton';

export default function addSidebarNav() {
  extend(IndexSidebar.prototype, 'navItems', function (items) {
    if (app.forum.attribute('blogAddSidebarNav') && app.forum.attribute('blogAddSidebarNav') !== '0') {
      items.add(
        'blog',
        <LinkButton icon="fas fa-comment" href={app.route('blog')}>
          {app.translator.trans('vadkuz-flarum2-blog.forum.blog')}
        </LinkButton>,
        15
      );
    }

    return items;
  });
}
