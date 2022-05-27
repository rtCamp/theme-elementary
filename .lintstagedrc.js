module.exports = {
	"**/*.php": [
		"npm run lint:php"
	],
	"**/*.js": [
		"npm run lint:js"
	],
	"**/*.{css,scss}": [
		"npm run lint:css"
	],
	"package.json": [
		"npm run lint:package-json"
	],
};
