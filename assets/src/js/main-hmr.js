// Import ALL JS files that needs to be watched by HMR
import './core-navigation';

// Import ALL CSS files that needs to be watched by HMR
import '../css/core-navigation.scss';
import '../css/styles.scss';

// Enable HMR
if (module.hot) {
	module.hot.accept();
}

console.log('HMR main loaded');
