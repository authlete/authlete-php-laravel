<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0, user-scalable=yes">
  <title>{{ $serviceName }} | Authorization Page</title>
  <link rel="stylesheet" href="/css/authlete/authorization.css">
</head>
<body class="font-default">
  <div id="page_title">{{ $serviceName }}</div>

  <div id="content">
    <h3 id="client-name">{{ $clientName }}</h3>
    <div class="indent">
      <img id="logo" src="{{ $logoUri }}" alt="[Logo] (150x150)">

      <div id="client-summary">
        <p>{{ $description }}</p>
        <ul id="client-link-list">
          @isset ($clientUri)
          <li><a target="_blank" href="{{ $clientUri }}">Homepage</a></li>
          @endisset
          @isset ($policyUri)
          <li><a target="_blank" href="{{ $policyUri }}">Policy</a></li>
          @endisset
          @isset ($tosUri)
          <li><a target="_blank" href="{{ $tosUri }}">Terms of Service</a></li>
          @endisset
        </ul>
      </div>

      <div style="clear: both;"></div>
    </div>

    @isset ($scopes)
    <h4 id="permissions">Permissions</h4>
    <div class="indent">
      <p>The application is requesting the following permissions.</p>
      <dl id="scope-list">
        @foreach ($scopes as $scope)
        <dt>{{ $scope['name'] }}</dt>
        <dd>{{ $scope['description'] }}</dd>
        @endforeach
      </dl>
    </div>
    @endisset

    <h4 id="authorization">Authorization</h4>
    <div class="indent">
      @isset ($userName)
      <p>Hello {{ $userName }},</p>
      @endisset
      <p>Do you grant authorization to the application?</p>

      <form id="authorization-form" action="/authorization/decision" method="POST">
        {{ csrf_field() }}
        @if ($authRequired)
        <input type="hidden" name="authRequired" value="true">
        <div id="login-fields" class="indent">
          <div id="login-prompt">Input Login ID and password.</div>
          <input type="text" id="loginId" name="loginId" placeholder="Login ID"
                 class="font-default" required value="{{ $loginId }}" {{ $loginIdReadOnly }}>
          <input type="password" id="password" name="password" placeholder="Password"
                 class="font-default" required>
        </div>
        @endif
        <div id="authorization-form-buttons">
          <input type="submit" name="authorized" id="authorize-button" value="Authorize" class="font-default"/>
          <input type="submit" name="denied"     id="deny-button"      value="Deny"      class="font-default"/>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
