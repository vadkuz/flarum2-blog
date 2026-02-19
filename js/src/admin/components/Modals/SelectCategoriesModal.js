import app from 'flarum/admin/app';
import FormModal from 'flarum/common/components/FormModal';
import Button from 'flarum/common/components/Button';
import Alert from 'flarum/common/components/Alert';
import saveSettings from 'flarum/admin/utils/saveSettings';
import Switch from 'flarum/common/components/Switch';

export default class SelectCategoriesModal extends FormModal {
  oninit(vnode) {
    super.oninit(vnode);

    this.blogCategoriesOriginal = app.data.settings.blog_tags ? app.data.settings.blog_tags.split('|') : [];
    this.blogCategories = app.data.settings.blog_tags ? app.data.settings.blog_tags.split('|') : [];

    this.hasChanges = false;
  }

  title() {
    return app.translator.trans('vadkuz-flarum2-blog.admin.select_categories_modal.title');
  }

  className() {
    return 'Modal modal-dialog FlarumBlog-TagsModal';
  }

  content() {
    return (
      <div>
        <div className="Modal-body">
          <p>
            {app.translator.trans('vadkuz-flarum2-blog.admin.select_categories_modal.description')}{' '}
            <a href={app.forum.attribute('baseUrl') + '/blog'} target={'_blank'}>
              {app.translator.trans('vadkuz-flarum2-blog.admin.select_categories_modal.visit_blog')}
            </a>
          </p>

          <table className={'FlarumBlog-TagsTable'}>
            <thead>
              <tr>
                <th width="35"></th>
                <th>{app.translator.trans('vadkuz-flarum2-blog.admin.select_categories_modal.tag_name')}</th>
                <th width="50"></th>
              </tr>
            </thead>
            <tbody>
              {app.store.all('tags').length === 0 && (
                <tr>
                  <td colSpan="3">{app.translator.trans('vadkuz-flarum2-blog.admin.select_categories_modal.no_tags')}</td>
                </tr>
              )}

              {app.store.all('tags').map((obj) => {
                // Skip all tags who aren't main categories
                if (obj.parent()) {
                  return;
                }

                // Toggle tag
                const toggleTag = () => {
                  const currentIndex = this.blogCategories.indexOf(obj.id());
                  this.hasChanges = true;

                  // Remove tag
                  if (currentIndex >= 0) {
                    this.blogCategories.splice(currentIndex, 1);
                  } else {
                    // Add tag
                    this.blogCategories.push(obj.id());
                  }
                };

                return (
                  <tr>
                    <td>
                      <i className={obj.icon()} />
                    </td>
                    <td onclick={toggleTag}>{obj.name()}</td>
                    <td>
                      <Switch state={this.blogCategories.indexOf(obj.id()) >= 0} onchange={toggleTag} />
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
        <div style="padding: 25px 30px; text-align: center;">
          <Button type="submit" className="Button Button--primary" loading={this.loading} disabled={this.loading}>
            {this.hasChanges
              ? app.translator.trans('vadkuz-flarum2-blog.admin.select_categories_modal.save_changes')
              : app.translator.trans('vadkuz-flarum2-blog.admin.select_categories_modal.close')}
          </Button>
        </div>
      </div>
    );
  }

  // Close or save setting
  onsubmit(e) {
    e.preventDefault();

    if (!this.hasChanges) {
      this.hide();
      return;
    }

    this.loading = true;

    // Validate tags and prevent ghost tags (deleted tags)
    let validBlogTags = [];

    this.blogCategories.map((tagId) => {
      if (app.store.getById('tags', tagId)) {
        validBlogTags.push(tagId);
      }
    });

    saveSettings({
      blog_tags: validBlogTags.join('|'),
    })
      .then(() => {
        app.alerts.show(
          Alert,
          {
            type: 'success',
          },
          app.translator.trans('core.admin.settings.saved_message')
        );

        this.hide();
      })
      .catch(() => {
        app.alerts.show(
          Alert,
          {
            type: 'error',
          },
          app.translator.trans('core.lib.error.generic_message')
        );
      })
      .then(() => {
        this.loaded();
      });
  }
}
