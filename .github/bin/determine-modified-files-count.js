/**
 * Determine the modified files count.
 *
 * Usage:
 * node determine-modified-files-count.js <file-path-pattern> <file paths delimited by newlines> [path/to/dir]
 *
 * Example:
 * node determine-modified-files-count.js "foo\/bar|bar*" "foo/bar/baz\nquux" "foo/bar"
 *
 * Output: 1
 */
const args = process.argv.slice(2);
const pattern = args[0];
const modifiedFiles = args[1].split('\n');
const dirInclude = args[2];

let count;

if ( 'all' === dirInclude ) {
	count = modifiedFiles.reduce((count, file) => {
		if (pattern.split('|').some(pattern => file.match(pattern))) {
			return count;
		}

		return count + 1;
	}, 0);
} else {
	count = modifiedFiles.filter( ( file ) => {
			return file.match( pattern );
	} ).length;
}

console.log( count );
