import 'ts-polyfill';
import 'cross-fetch/polyfill';
import './index.css';

import React, { Suspense, useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import './i18n';
import App from './App';
import * as serviceWorker from './serviceWorker';

function Index() {
	return (<App />);
}

ReactDOM.render(
	<Suspense fallback={<h2>Loading...</h2>}>
		<Index />
	</Suspense>,
	document.getElementById('root'),
);

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
