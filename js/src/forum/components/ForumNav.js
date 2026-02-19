import Component from 'flarum/common/Component';
import app from 'flarum/forum/app';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import SelectDropdown from 'flarum/common/components/SelectDropdown';

export default class ForumNav extends Component {
  view() {
    return (
      <div className="BlogForumNav BlogSideWidget">
        <h3>{app.translator.trans('vadkuz-flarum2-blog.forum.forum_nav')}</h3>
        <nav className="IndexPage-nav sideNav">
          <SelectDropdown buttonClassName="Button" className="App-titleControl">
            {this.navItems().toArray()}
          </SelectDropdown>
        </nav>
      </div>
    );
  }

  navItems() {
    return IndexSidebar.prototype.navItems();
  }
}
