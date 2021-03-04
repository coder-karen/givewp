import { useState, Fragment } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

import TextControl from '../text-control';
import Button from '../button';

import { loginWithAPI, verifyEmailWithAPI } from './utils';
import { getWindowData } from '../../utils';

import './style.scss';

const AuthModal = () => {
	const [ email, setEmail ] = useState( '' );
	const [ login, setLogin ] = useState( '' );
	const [ password, setPassword ] = useState( '' );
	const [ loginError, setLoginError ] = useState( null );
	const [ loggingIn, setLoggingIn ] = useState( false );
	const [ verifyingEmail, setVerifyingEmail ] = useState( false );
	const [ emailSent, setEmailSent ] = useState( false );
	const [ emailError, setEmailError ] = useState( null );
	const emailAccessEnabled = getWindowData( 'emailAccessEnabled' );
	const loggedInWithoutDonor = getWindowData( 'loggedInWithoutDonor' );

	const handleLogin = async( e ) => {
		e.preventDefault();
		if ( login && password ) {
			setLoggingIn( true );
			// eslint-disable-next-line camelcase
			const { status, response, body_response } = await loginWithAPI( {
				login,
				password,
			} );

			if ( status === 200 ) {
				window.location.reload();
			} else {
				setLoggingIn( false );
				setLoginError( body_response.message );
				if ( response === 'unidentified_login' ) {
					setLogin( '' );
					setPassword( '' );
				} else {
					setPassword( '' );
				}
			}
		}
	};

	const handleVerifyEmail = async() => {
		if ( email ) {
			setVerifyingEmail( true );
			// eslint-disable-next-line camelcase
			const { status, body_response } = await verifyEmailWithAPI( {
				email,
			} );

			if ( status === 200 ) {
				setVerifyingEmail( false );
				setEmailSent( true );
			} else {
				setVerifyingEmail( false );
				setEmailError( body_response.message );
				setEmail( '' );
			}
		}
	};

	return (
		<div className="give-donor-profile__auth-modal">
			<div className="give-donor-profile__auth-modal-frame">
				<div className="give-donor-profile__auth-modal-heading">
					{ __( 'Log in to see your donor dashboard', 'give' ) }
				</div>
				<div className="give-donor-profile__auth-modal-content">
					{ loggedInWithoutDonor && (
						<div className="give-donor-profile__auth-modal-notice">
							{ __( 'The account you are currently logged into the site with does not have an associated donor profile. Donate now or contact the site administrator associate this WordPress user with a donor profile.' ) }
						</div>
					) }
					{ emailAccessEnabled && (
						<Fragment>
							<div className="give-donor-profile__auth-modal-instruction">
								{ __( 'Enter your email below and we\'ll send you a link to access your donor dashboard', 'give' ) }
							</div>
							<form className="give-donor-profile__auth-modal-form">
								<TextControl icon="envelope" value={ email } onChange={ ( value ) => setEmail( value ) } />
								<div className="give-donor-profile__auth-modal-row">
									<Button onClick={ () => handleVerifyEmail() }>
										{ emailSent === false ? __( 'Verify Email', 'give' ) : __( 'Email Sent', 'give' ) }
										{ emailSent === false && <FontAwesomeIcon className={ verifyingEmail ? 'give-donor-profile__auth-modal-spinner' : '' } icon={ verifyingEmail ? 'spinner' : 'chevron-right' } fixedWidth /> }
									</Button>
									{ emailError && (
										<div className="give-donor-profile__auth-modal-error">
											{ emailError }
										</div>
									) }
								</div>
							</form>
							<div className="give-donor-profile__auth-modal-seperator" />
						</Fragment>
					) }
					<div className="give-donor-profile__auth-modal-instruction">
						{ emailAccessEnabled && (
							<Fragment>
								{ __( 'Already have an account?', 'give' ) } <br />
							</Fragment>
						) }
						{ __( 'Log in below to access your dashboard', 'give' ) }
					</div>
					<form className="give-donor-profile__auth-modal-form" onSubmit={ ( e ) => handleLogin( e ) }>
						<TextControl icon="user" value={ login } onChange={ ( value ) => setLogin( value ) } />
						<TextControl icon="lock" type="password" value={ password } onChange={ ( value ) => setPassword( value ) } />
						<div className="give-donor-profile__auth-modal-row">
							<Button type="submit">
								{ __( 'Log in', 'give' ) }
								<FontAwesomeIcon className={ loggingIn ? 'give-donor-profile__auth-modal-spinner' : '' } icon={ loggingIn ? 'spinner' : 'chevron-right' } fixedWidth />
							</Button>
							{ loginError && (
								<div className="give-donor-profile__auth-modal-error">
									{ loginError }
								</div>
							) }
						</div>
					</form>
				</div>
			</div>
		</div>
	);
};

export default AuthModal;