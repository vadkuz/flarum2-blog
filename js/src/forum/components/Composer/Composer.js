import Component from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';
import TextEditor from 'flarum/common/components/TextEditor';
import ComposerPreview from './ComposerPreview';
import app from 'flarum/forum/app';

export default class Composer extends Component {
  oninit(vnode) {
    super.oninit(vnode);
    this.composer = this.attrs.composer;
    this.previewContent = false;
  }

  // Render
  view() {
    this.composer = this.attrs.composer;
    const content = this.composer?.fields?.content ? this.composer.fields.content() : '';
    const hasContent = content !== '';
    const loading = !!this.attrs.disabled;

    return (
      <div className={`Flarum-Blog-Composer ${loading ? 'Flarum-Blog-Composer-Loading' : ''}`}>
        <div className={'Flarum-Blog-Composer-tabs'}>
          <Button className={!this.previewContent && 'AricleComposerButtonSelected'} onclick={() => (this.previewContent = false)}>
            {app.translator.trans('vadkuz-flarum2-blog.forum.composer.write')}
          </Button>
          <Button className={this.previewContent && 'AricleComposerButtonSelected'} onclick={() => (this.previewContent = true)}>
            {app.translator.trans('vadkuz-flarum2-blog.forum.composer.view')}
          </Button>
        </div>

        <div className={`Composer Flarum-Blog-Composer-body ${this.previewContent ? 'Flarum-Blog-Composer-HideEditor' : ''}`}>
          {this.previewContent && (
            <div className={'Flarum-Blog-Composer-preview'}>
              {!hasContent && app.translator.trans('vadkuz-flarum2-blog.forum.composer.nothing_to_preview')}

              <ComposerPreview content={this.composer.fields.content()} />
            </div>
          )}

          {TextEditor.component({
            submitLabel: this.attrs.submitLabel || app.translator.trans('core.forum.composer_edit.submit_button'),
            placeholder: this.attrs.placeholder,
            disabled: loading,
            composer: this.composer,
            onchange: this.composer?.fields?.content,
            onsubmit: this.onsubmit.bind(this),
            value: content,
          })}
        </div>
      </div>
    );
  }

  // Submit trigger
  onsubmit() {
    if (this.attrs.onsubmit) {
      this.attrs.onsubmit();
    }
  }
}
