Freeder.EntryController = Ember.ObjectController.extend({
	isRead: function (key, value) {
		var model = this.get('model');

		if (value === undefined) {
			// property being used as a getter
			return model.get('isRead');
		} else {
			// property being used as a setter
			model.set('isRead', value);
			model.save();
			return value;
	    }
	}.property('model.isRead')
});