module.exports = {
	'**/*.php': ['npm run lint:php'],
	'**/*.js': ['npm run lint:js:files'],
	'**/*.{css,scss}': ['npm run lint:css:files'],
	'package.json': ['npm run lint:package-json'],
};
