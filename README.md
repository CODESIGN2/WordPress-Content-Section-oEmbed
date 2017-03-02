## WordPress-Content-Section-oEmbed
Allows same-site oEmbed of content sections plugins (gives a better visual editor experience)

### Usage
Requires https://github.com/CODESIGN2/WordPress-Content-Sections/ installed to do anything

usage is pretty much as simple as pasting in valid ajax content section title

`http://yoursite.com/wp-admin/admin-ajax.php?action=get_content_section&name={your-content-section-name}`

### Attribution

Inspired by past works

 * https://github.com/CODESIGN2/WordPress-Content-Sections/issues/1
 * https://make.wordpress.org/core/2016/12/24/idea-uniform-resource-identifiers-as-an-alternative-to-shortcodes/
 * https://github.com/WordPress/gutenberg/issues/150

### Alternatives
A Visual shortcode editor would be another way to achieve, but so far an elegant solution for that has eluded CD2, as the API modified, is poorly documented and represents more potential change.

### What it does?
This is not for making your site oEmbed into other sites, it's for making the content section plugin oEmbed into the same site it's hosted on.

### Contributing
There may be better ways to do this, so submit an issue, PR, fork, blog about how to do it better etc. Also check branches as there are currently two implementations which need a bit of love, documentation.

### Why a plugin of a plugin
Less bloat, and no need to think about the UI of making the feature optional.
