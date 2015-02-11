Freeder.Entry = DS.Model.extend({
	title: DS.attr('string'),
	source: DS.attr('string'),
	author: DS.attr('string'),
	body: DS.attr('string'),
	isRead: DS.attr('boolean')
});

Freeder.Entry.FIXTURES = [
	{
		id: 1,
		title: "Lorem Ipsum",
		source: "Tradition",
		author: "Unknown",
		body: "Dolor sit amet, consectetur adipiscing elit.",
		isRead: true
	},
	{
		id: 2,
		title: "Rupture",
		source: "Communications n°30",
		author: "FF",
		body: "Il n'est de rupture si forte qu'elle ne maintienne au moins par rapport à ce avec quoi elle rompt le lien ce que constitue la marque de cette rupture.",
		isRead: false
	}
];