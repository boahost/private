<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $errorMessage . $errorMessageHighlight }}</title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
	<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css'>

	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: Roboto, sans-serif;

            /* Website main color */
			background: #243949;
		}

		.container {
			width: 100%;
			height: 100%;

			display: flex;
			align-items: center;
			justify-content: center;
		}

		@media only screen and (max-width: 600px) {
			.error-message-container {
				display: flex;
				flex-direction: column;
			}

			.error-message {
				margin-left: 0 !important;
				margin-top: 2vw !important;
				padding-left: 0 !important;

				border-left: 0 !important;
			}

			.error-message-container i {
				font-size: 10vw !important;
			}

			.error-message h1 {
				font-size: 7vw !important;
			}

			.error-message h2 {
				font-size: 4.5vw !important;
			}
		}

		.error-code h1 {
			font-size: 36vw;
			color: rgba(0, 0, 0, 0.07);
		}

		.error-message-container {
			position: absolute;
		}

		.error-message-container i {
			color: white;
			font-size: 6.6vw;
		}

		.error-message {
			text-align: center;

			margin-left: 2.4vw;
			padding-left: 2.4vw;

			border-left: 3px white solid;
		}

		.error-message h1 {
			color: white;
			font-weight: 400;
			font-size: 4.2vw;
		}

		.error-message h2 {
			margin-top: 0.6vw;

			font-weight: 400;
			font-size: 2.7vw;
			color: rgba(255, 255, 255, 0.55);
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="error-code">
			<h1>{{ $errorCode }}</h1>
		</div>

		<div class="error-message-container container">
			<i class="fas {{ isset($messageIcon) ? $messageIcon : 'fa-question' }}"></i>

			<div class="error-message">
				<h1>
                    {{ isset($errorMessage) ? $errorMessage : 'Erro' }}
                    <b>{{ isset($errorMessageHighlight) ? $errorMessageHighlight : ' desconhecido' }}</b>
                </h1>
                @if (isset($solutionMessage) && isset($solutionMessageHighlight))
                    <h2>
                        {{ $solutionMessage }}
                        <b>{{ $solutionMessageHighlight }}</b>
                    </h2>
                @endif
			</div>
		</div>
	</div>
</body>
</html>
