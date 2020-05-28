import React, { useEffect, useState } from 'react';
import makeStyles from '@material-ui/styles/makeStyles';
import HomePage from './components/HomePage';
import Api from './services/Api';

const useStyles = makeStyles(theme => ({
	root: {
		display: 'flex',
		flexDirection: 'column',
		background: '#f5f6fa',
		height: '100vh',
	},
}));

export default function App() {
	const classes = useStyles();
	const page = { title: 'Menu' }
	const title = page.title;

	// Update page title
	useEffect(() => {
		const win = window as any;
		if (!win._defaultTitle) win._defaultTitle = document.title;
		document.title = title || win._defaultTitle;
	}, [title]);

	return (
		<div className={classes.root}>Hello</div>
	);
};
