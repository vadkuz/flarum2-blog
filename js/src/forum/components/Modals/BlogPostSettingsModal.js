import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import Switch from 'flarum/common/components/Switch';

export default class BlogPostSettingsModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    if (this.attrs.article) {
      this.meta = this.attrs.article && this.attrs.article.blogMeta() ? this.attrs.article.blogMeta() : app.store.createRecord('blogMeta');
    } else {
      this.meta = this.attrs.meta ? this.attrs.meta : app.store.createRecord('blogMeta');
    }

    this.isNew = !this.meta.exists;

    this.summary = Stream(this.meta.summary() || '');

    this.featuredImage = Stream(this.meta.featuredImage() || '');

    this.isFeatured = Stream(this.meta.isFeatured() || false);
    this.isSized = Stream(this.meta.isSized() || false);
    this.isPendingReview = Stream(this.meta.isPendingReview() || false);

    this.fofUploadState = {
      loading: false,
      error: false,
      FileManagerModal: null,
      uploader: null,
    };
  }

  className() {
    return 'Modal--small Support-Modal';
  }

  title() {
    return app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.title');
  }

  content() {
    return (
      <div className="Modal-body">
        <form className="Form" onsubmit={this.onsubmit.bind(this)}>
          {this.fields().toArray()}
        </form>
      </div>
    );
  }

  canUseFoFUpload() {
    return Boolean(app.forum.attribute('fof-upload.canUpload'));
  }

  findUploadedFile(fileId) {
    if (!fileId) return null;

    return app.store.getById('files', fileId) || app.store.getById('shared-files', fileId) || null;
  }

  getUploadedFileUrl(file) {
    if (!file) return null;

    if (typeof file.url === 'function') return file.url();

    return file.data?.attributes?.url || null;
  }

  async openFoFUploadModal() {
    if (this.fofUploadState.loading) return;

    try {
      if (!this.fofUploadState.FileManagerModal || !this.fofUploadState.uploader) {
        this.fofUploadState.loading = true;
        this.fofUploadState.error = false;
        m.redraw();

        const [{ default: FileManagerModal }, { default: Uploader }] = await Promise.all([
          flarum.reg.asyncModuleImport('fof-upload/forum/components/FileManagerModal'),
          flarum.reg.asyncModuleImport('fof-upload/forum/handler/Uploader'),
        ]);

        this.fofUploadState.FileManagerModal = FileManagerModal;
        this.fofUploadState.uploader = new Uploader();
      }

      app.modal.show(
        this.fofUploadState.FileManagerModal,
        {
          uploader: this.fofUploadState.uploader,
          onSelect: (selectedFileIds = []) => {
            const selectedFile = this.findUploadedFile(selectedFileIds[0]);
            const selectedFileUrl = this.getUploadedFileUrl(selectedFile);

            if (selectedFileUrl) {
              this.featuredImage(selectedFileUrl);
            }
          },
        },
        true
      );
    } catch (error) {
      // eslint-disable-next-line no-console
      console.error('[vadkuz/flarum2-blog] FoF Upload integration failed', error);
      this.fofUploadState.error = true;
      app.alerts.show(
        {
          type: 'error',
        },
        app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.image.upload_unavailable')
      );
    } finally {
      this.fofUploadState.loading = false;
      m.redraw();
    }
  }

  fields() {
    const items = new ItemList();

    items.add(
      'summary',
      <div className="Form-group">
        <label>{app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.summary.title')}:</label>
        <textarea
          className="FormControl"
          style={{
            maxWidth: '100%',
            minWidth: '100%',
            width: '100%',
            minHeight: '120px',
          }}
          bidi={this.summary}
          placeholder={app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.summary.placeholder')}
        />

        <small>{app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.summary.helper_text')}</small>
      </div>,
      30
    );

    const fofUploadEnabled = this.canUseFoFUpload();

    items.add(
      'image',
      <div className="Form-group VadkuzBlog-ArticleImage">
        <label>{app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.image.title')}:</label>
        <div data-upload-enabled={fofUploadEnabled}>
          <input type="text" className="FormControl" bidi={this.featuredImage} placeholder="https://" />
          {fofUploadEnabled ? (
            <Button
              className="Button Button--icon"
              loading={this.fofUploadState.loading}
              disabled={this.fofUploadState.loading}
              onclick={() => this.openFoFUploadModal()}
              icon="fas fa-cloud-upload-alt"
            />
          ) : null}
        </div>

        <small>{app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.image.helper_text')}</small>

        {this.featuredImage() !== '' && (
          <img
            src={this.featuredImage()}
            alt="Article image"
            title={app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.image.title')}
            style={{ width: '100%', marginTop: '15px' }}
          />
        )}
      </div>,
      30
    );

    items.add(
      'sized',
      <div className="Form-group">
        {Switch.component(
          {
            state: this.isSized() == true,
            onchange: (val) => {
              this.isSized(val);
            },
          },
          [
            <b>{app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.highlight.title')}</b>,
            <div className="helpText" style={{ fontWeight: 500 }}>
              {app.translator.trans('vadkuz-flarum2-blog.forum.article_settings.fields.highlight.helper_text')}
            </div>,
          ]
        )}
      </div>,
      -10
    );

    items.add(
      'submit',
      <div className="Form-group">
        {Button.component(
          {
            type: 'submit',
            className: 'Button Button--primary SupportModal-save',
            loading: this.loading,
          },
          app.translator.trans('core.forum.composer_edit.submit_button')
        )}
      </div>,
      -10
    );

    return items;
  }

  submitData() {
    return {
      summary: this.summary(),
      featuredImage: this.featuredImage(),
      isFeatured: this.isFeatured(),
      isSized: this.isSized(),
      isPendingReview: this.isPendingReview(),
      relationships:
        this.isNew && !this.attrs.isComposer
          ? {
              discussion: this.attrs.article,
            }
          : null,
    };
  }

  onsubmit(e) {
    e.preventDefault();

    // Submit data
    if (this.attrs.onsubmit) {
      // Update attributes
      this.meta.pushData({
        attributes: this.submitData(),
      });

      // Push
      this.attrs.onsubmit(this.meta);

      this.hide();
      return;
    }

    this.loading = true;

    this.meta.save(this.submitData()).then(
      () => {
        if (this.attrs.article) {
          this.attrs.article.pushData({
            relationships: {
              blogMeta: this.meta,
            },
          });
        }

        this.hide();
        m.redraw();
      },
      (response) => {
        this.loading = false;
        this.handleErrors(response);
      }
    );
  }
}
