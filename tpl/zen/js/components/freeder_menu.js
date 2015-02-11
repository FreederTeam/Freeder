Freeder.FreederMenuComponent = Ember.Component.extend({
	actions: {
		'show-more': function () {
			this.toggleProperty('isShowingSubmenu')
		}
	}
})