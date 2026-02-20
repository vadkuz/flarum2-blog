import { extend } from 'flarum/common/extend';
import app from 'flarum/admin/app';
import BasicsPage from 'flarum/admin/components/BasicsPage';
import PermissionGrid from 'flarum/admin/components/PermissionGrid';
import BlogSettings from './pages/BlogSettings';
import applyRuTranslations from '../common/translations/ru';

app.initializers.add('vadkuz-flarum2-blog', () => {
  applyRuTranslations(app);

  // Prefer support.source for admin "Source" link and normalize trailing ".git".
  const extension = app.data?.extensions?.['vadkuz-flarum2-blog'];
  const declaredSource = extension?.support?.source;

  if (extension && typeof declaredSource === 'string' && declaredSource.length) {
    const normalizedSource = declaredSource.replace(/\.git$/, '');

    extension.links = extension.links || {};
    extension.links.source = normalizedSource;

    if (extension.source && typeof extension.source === 'object') {
      extension.source.url = normalizedSource;
    }
  }

  // Register extension settings page
  app.registry.for('vadkuz-flarum2-blog').registerPage(BlogSettings);

  app.registry
    .for('vadkuz-flarum2-blog')
    .registerPermission(
      {
        icon: 'fas fa-pencil-alt',
        label: app.translator.trans('vadkuz-flarum2-blog.admin.permissions.write_articles'),
        permission: 'blog.writeArticles',
      },
      'blog',
      90
    )
    .registerPermission(
      {
        icon: 'far fa-star',
        label: app.translator.trans('vadkuz-flarum2-blog.admin.permissions.auto_approve_posts'),
        permission: 'blog.autoApprovePosts',
      },
      'blog',
      90
    )
    .registerPermission(
      {
        icon: 'far fa-thumbs-up',
        label: app.translator.trans('vadkuz-flarum2-blog.admin.permissions.approve_posts'),
        permission: 'blog.canApprovePosts',
      },
      'blog',
      90
    );

  // Add addPermissions
  extend(PermissionGrid.prototype, 'permissionItems', function (items) {
    const extensionId = this.attrs?.extensionId;
    const permissions = extensionId
      ? app.registry.getExtensionPermissions(extensionId, 'blog')?.toArray?.() ?? []
      : app.registry.getAllPermissions('blog').toArray();

    // Add blog permissions
    items.add(
      'blog',
      {
        label: app.translator.trans('vadkuz-flarum2-blog.admin.blog'),
        children: permissions,
      },
      80
    );
  });

  extend(BasicsPage.prototype, 'homePageItems', (result, itemsArg) => {
    const items = itemsArg && typeof itemsArg.add === 'function' ? itemsArg : result;

    if (!items || typeof items.add !== 'function') {
      return;
    }

    items.add('blog', {
      path: '/blog',
      label: app.translator.trans('vadkuz-flarum2-blog.admin.blog'),
    });
  });
});
