var data = [
	{
		"author": "John Doe",
		"content": "Lorem ipsum dolor sit amet, consectatur adipiscing elit"
	},

	{
		"author": "Foo Bar",
		"content": "Que ne suis-je la fougère, ou sur la **fin** d'un beau jour"
	}
];

var converter = new Showdown.converter();



var CommentBox = React.createClass({displayName: "CommentBox",
	getInitialState: function() {
		return { data: [] };
	},

	loadData: function() {
		$.ajax({
			url: this.props.url,
			dataType: 'json',
			success: function(data) {
				this.setState({ data: data.comments })
			}.bind(this),
			error: function(xhr, status, err) {
				console.error(this.props.url, status, err.toString());
			}.bind(this)
		});
	},

	componentDidMount: function() {
		this.loadData();
		if (this.props.pollInterval > 0) {
			setInterval(this.loadData, this.props.pollInterval);
		}
	},

	handleCommentSubmit: function(comment) {
		var newData = this.state.data;
		newData.push(comment);
		this.setState({ data: newData })
	},

	render: function() {
		return (
			React.createElement("div", {className: "comment-box"}, 
				React.createElement("h1", null, "Comments"), 
				React.createElement(CommentList, {data: this.state.data}), 
				React.createElement(CommentForm, {onCommentSubmit: this.handleCommentSubmit})
			)
		);
	}
});


var CommentList = React.createClass({displayName: "CommentList",
	render: function() {
		var comments = this.props.data.map(function(comment) {
			return (
				React.createElement(Comment, {author: comment.author}, 
					comment.content
				)
			);
		});

		if (this.props.data.length == 0) {
			comments = (
				React.createElement("em", null, "Loading…")
			);
		}

		return (
			React.createElement("div", {className: "comment-list"}, 
				comments
			)
		);
	}
});


var Comment = React.createClass({displayName: "Comment",
	render: function() {
		return (
			React.createElement("div", {className: "comment"}, 
				React.createElement("h2", null, this.props.author), 
				React.createElement("div", {dangerouslySetInnerHTML: {__html: converter.makeHtml(this.props.children)}})
			)
		);
	}
});


var CommentForm = React.createClass({displayName: "CommentForm",
	handleSubmit: function(e) {
		e.preventDefault();
		var author = this.refs.author.getDOMNode().value.trim();
		var content = this.refs.content.getDOMNode().value.trim();
		if (!author || !content) {
			return;
		}
		this.props.onCommentSubmit({ author: author, content: content });
		this.refs.author.getDOMNode().value = '';
		this.refs.content.getDOMNode().value = '';
	},

	render: function() {
		return (
			React.createElement("form", {className: "comment-form", onSubmit: this.handleSubmit}, 
				React.createElement("input", {type: "text", placeholder: "Name", ref: "author"}), 
				React.createElement("textarea", {placeholder: "Your comment…", ref: "content"}), 
				React.createElement("input", {type: "submit", value: "Send"})
			)
		);
	}
});



var Freeder = React.createClass({displayName: "Freeder",
	render: function() {
		return (
			React.createElement("div", null, 
				React.createElement(Menu, null), 
				React.createElement(SubMenu, null), 
				React.createElement(Main, null)
			)
		);
	}
});


var Menu = React.createClass({displayName: "Menu",
	render: function() {
		return (
			React.createElement("aside", {className: "menu"}, 
				React.createElement("div", {className: "menu--wrapper"}, 
					React.createElement("div", {className: "menu--logo"}, 
						React.createElement("a", {href: ""}, React.createElement("img", {alt: "freeder", src: "img/logo.svg", className: "icon"}))
					), 
					
					React.createElement("nav", {className: "menu--nav vertical-nav"}, 
						React.createElement("div", {className: "vertical-nav--arrow"}, React.createElement("img", {className: "icon", alt: "<", src: "img/arrow.svg"})), 
						React.createElement("a", {href: "/", className: "vertical-nav--item"}, React.createElement("img", {className: "icon", alt: "Home", src: "img/home.svg"})), 

						React.createElement("div", {className: "vertical-nav--arrow"}, React.createElement("img", {className: "icon", alt: "<", src: "img/arrow.svg"})), 
						React.createElement("button", {className: "vertical-nav--item toggle-submenu", id: "open-submenu-list"}, React.createElement("img", {className: "icon", alt: "Feeds", src: "img/list.svg"})), 

						React.createElement("div", {className: "vertical-nav--arrow"}, React.createElement("img", {className: "icon", alt: "<", src: "img/arrow.svg"})), 
						React.createElement("a", {href: "settings.php", className: "vertical-nav--item"}, React.createElement("img", {className: "icon", alt: "Settings", src: "img/settings.svg"})), 

						React.createElement("div", {className: "vertical-nav--arrow"}, React.createElement("img", {className: "icon", alt: "<", src: "img/arrow.svg"})), 
						React.createElement("button", {className: "vertical-nav--item"}, React.createElement("img", {className: "icon", alt: "More", src: "img/plus_big.svg"}))
					)
				)
			)
		);
	}
});


var SubMenu = React.createClass({displayName: "SubMenu",
	render: function() {
		return (
			React.createElement("aside", {className: ":submenu :feed-submenu isShowingSubmenu:open"}, 
				React.createElement("div", {className: "submenu--wrapper"}, 
					React.createElement("div", {className: "submenu--search searchbar"}, 
						React.createElement("input", {className: "searchbar--input", type: "search", placeholder: "Search…"}), 
						React.createElement("img", {className: "searchbar--icon", alt: "", src: "img/search.svg"})
					), 

					React.createElement("section", {id: "submenu-list"}, 

						React.createElement("div", {className: "submenu--section"}, 
							React.createElement("h4", {className: "submenu--section-title"}, "Feeds"), 
							React.createElement("ul", {className: "submenu--link-list"}, 
								React.createElement("li", null, 
									React.createElement("a", {href: "/%feed%/{$value['id']}", title: "{$value['description']}"}, "Title"), 
									React.createElement("span", {className: "coutner"}, "?")
								)
							)
						)

					), 

					React.createElement("section", {id: "submenu-more"}, 
						React.createElement("div", {className: "submenu--section"}, 
							React.createElement("h4", {className: "submenu--section-title"}, "Foo"), 
							React.createElement("a", {href: "refresh.php"}, "Synchronize")
						)
					)

				)
			)
		);
	}
});


var Main = React.createClass({displayName: "Main",
	render: function() {
		return (
			React.createElement("main", {className: "main"}, 

				React.createElement("article", {className: "article"}, 
					React.createElement("div", {className: "article--wrapper"}, 
						React.createElement("h1", {className: "article--title"}, "Welcome to Freeder!"), 
						React.createElement("h2", {className: "article--info"}, "It seems that you do not follow any feed"), 
						React.createElement("div", {className: "article--content"}, 
							React.createElement("p", null, 
								"You can add a new feed through the ", React.createElement("a", {href: "/settings.php#tab-feeds"}, "Feeds settings"), " page.", React.createElement("br", null), 
								"You can also import your feed from another reader thanks to ", React.createElement("a", {href: "/settings.php#tab-import"}, "OPML import"), "."
							)
						), 
						React.createElement("h1", {className: "article--title"}, "Nothing new!"), 
						React.createElement("h2", {className: "article--info"}, "You have no more unread entry")
						
					)
				), 

				React.createElement(Article, {id: 42, title: "Lorem ipsum", url: "http://foo.bar", feed: {title: "Dolor sit amet", url: "http://foo.bar"}, author: "Author", datetime: "Publication date"}, 
					"Test"
				)
			)
		);
	}
});


var Article = React.createClass({displayName: "Article",
	render: function() {
		var entryId = 'entry-' + this.props.id;
		return (
			React.createElement("article", {className: "article", id: entryId}, 
				React.createElement("div", {className: "article--wrapper"}, 
					React.createElement("nav", {className: "article--nav horizontal-nav"}, 
						React.createElement("div", {className: "article--nav-wrapper"}, 
							React.createElement("button", {className: "horizontal-nav--item"}, React.createElement("img", {className: "icon", alt: "More", src: "img/plus.svg"})), 
							React.createElement("button", {className: "horizontal-nav--item"}, React.createElement("img", {className: "icon", alt: "Share", src: "img/share.svg"})), 
							React.createElement("button", {className: "horizontal-nav--item close-btn"}, React.createElement("img", {className: "icon", alt: "Close", src: "img/close.svg"}))
						)
					), 
					React.createElement("h1", {className: "article--title"}, 
						React.createElement("a", {href: this.props.url}, this.props.title)
					), 
					React.createElement("h2", {className: "article--info"}, 
						React.createElement("a", {href: this.props.feed.url}, this.props.feed.title)
					), 
					React.createElement("p", {className: "article--subinfo"}, 
						this.props.author, React.createElement("br", null), 
						this.props.datetime
					), 
					React.createElement("div", {className: "article--content"}, 
						this.props.children
					)
				)
			)
		);
	}
});



React.render(
  React.createElement(Freeder, null),
  document.getElementById('content')
);



