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



var CommentBox = React.createClass({
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
			<div className="comment-box">
				<h1>Comments</h1>
				<CommentList data={this.state.data} />
				<CommentForm onCommentSubmit={this.handleCommentSubmit} />
			</div>
		);
	}
});


var CommentList = React.createClass({
	render: function() {
		var comments = this.props.data.map(function(comment) {
			return (
				<Comment author={comment.author}>
					{comment.content}
				</Comment>
			);
		});

		if (this.props.data.length == 0) {
			comments = (
				<em>Loading…</em>
			);
		}

		return (
			<div className="comment-list">
				{comments}
			</div>
		);
	}
});


var Comment = React.createClass({
	render: function() {
		return (
			<div className="comment">
				<h2>{this.props.author}</h2>
				<div dangerouslySetInnerHTML={{__html: converter.makeHtml(this.props.children)}} />
			</div>
		);
	}
});


var CommentForm = React.createClass({
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
			<form className="comment-form" onSubmit={this.handleSubmit}>
				<input type="text" placeholder="Name" ref="author" />
				<textarea placeholder="Your comment…" ref="content" />
				<input type="submit" value="Send" />
			</form>
		);
	}
});



var Freeder = React.createClass({
	render: function() {
		return (
			<div>
				<Menu />
				<SubMenu />
				<Main />
			</div>
		);
	}
});


var Menu = React.createClass({
	render: function() {
		return (
			<aside className="menu">
				<div className="menu--wrapper">
					<div className="menu--logo">
						<a href=""><img alt="freeder" src="img/logo.svg" className="icon" /></a>
					</div>
					
					<nav className="menu--nav vertical-nav">
						<div className="vertical-nav--arrow"><img className="icon" alt="<" src="img/arrow.svg"/></div>
						<a href="/" className="vertical-nav--item"><img className="icon" alt="Home" src="img/home.svg"/></a>

						<div className="vertical-nav--arrow"><img className="icon" alt="<" src="img/arrow.svg"/></div>
						<button className="vertical-nav--item toggle-submenu" id="open-submenu-list"><img className="icon" alt="Feeds" src="img/list.svg"/></button>

						<div className="vertical-nav--arrow"><img className="icon" alt="<" src="img/arrow.svg"/></div>
						<a href="settings.php" className="vertical-nav--item"><img className="icon" alt="Settings" src="img/settings.svg"/></a>

						<div className="vertical-nav--arrow"><img className="icon" alt="<" src="img/arrow.svg"/></div>
						<button className="vertical-nav--item"><img className="icon" alt="More" src="img/plus_big.svg"/></button>
					</nav>
				</div>
			</aside>
		);
	}
});


var SubMenu = React.createClass({
	render: function() {
		return (
			<aside className=":submenu :feed-submenu isShowingSubmenu:open">
				<div className="submenu--wrapper">
					<div className="submenu--search searchbar">
						<input className="searchbar--input" type="search" placeholder="Search…"/>
						<img className="searchbar--icon" alt="" src="img/search.svg"/>
					</div>

					<section id="submenu-list">

						<div className="submenu--section">
							<h4 className="submenu--section-title">Feeds</h4>
							<ul className="submenu--link-list">
								<li>
									<a href="/%feed%/{$value['id']}" title="{$value['description']}">Title</a>
									<span className="coutner">?</span>
								</li>
							</ul>
						</div>

					</section>

					<section id="submenu-more">
						<div className="submenu--section">
							<h4 className="submenu--section-title">Foo</h4>
							<a href="refresh.php">Synchronize</a>
						</div>
					</section>

				</div>
			</aside>
		);
	}
});


var Main = React.createClass({
	render: function() {
		return (
			<main className="main">

				<article className="article">
					<div className="article--wrapper">
						<h1 className="article--title">Welcome to Freeder!</h1>
						<h2 className="article--info">It seems that you do not follow any feed</h2>
						<div className="article--content">
							<p>
								You can add a new feed through the <a href="/settings.php#tab-feeds">Feeds settings</a> page.<br/>
								You can also import your feed from another reader thanks to <a href="/settings.php#tab-import">OPML import</a>.
							</p>
						</div>
						<h1 className="article--title">Nothing new!</h1>
						<h2 className="article--info">You have no more unread entry</h2>
						
					</div>
				</article>

				<Article id={42} title="Lorem ipsum" url="http://foo.bar" feed={{title: "Dolor sit amet", url: "http://foo.bar"}} author="Author" datetime="Publication date">
					Test
				</Article>
			</main>
		);
	}
});


var Article = React.createClass({
	render: function() {
		var entryId = 'entry-' + this.props.id;
		return (
			<article className="article" id={entryId}>
				<div className="article--wrapper">
					<nav className="article--nav horizontal-nav">
						<div className="article--nav-wrapper">
							<button className="horizontal-nav--item"><img className="icon" alt="More" src="img/plus.svg"/></button>
							<button className="horizontal-nav--item"><img className="icon" alt="Share" src="img/share.svg"/></button>
							<button className="horizontal-nav--item close-btn"><img className="icon" alt="Close" src="img/close.svg"/></button>
						</div>
					</nav>
					<h1 className="article--title">
						<a href={this.props.url}>{this.props.title}</a>
					</h1>
					<h2 className="article--info">
						<a href={this.props.feed.url}>{this.props.feed.title}</a>
					</h2>
					<p className="article--subinfo">
						{this.props.author}<br/>
						{this.props.datetime}
					</p>
					<div className="article--content">
						{this.props.children}
					</div>
				</div>
			</article>
		);
	}
});



React.render(
  <Freeder />,
  document.getElementById('content')
);



