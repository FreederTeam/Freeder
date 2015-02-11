Freeder.Router.map(function() {
	this.resource('entries', { path: '/' } )
});

Freeder.EntriesRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('entry');
  }
});

