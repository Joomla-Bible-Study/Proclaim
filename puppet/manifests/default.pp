exec { "apt-get update":
	path => "/usr/bin",
}

package { "apache2":
	ensure => present,
	require => Exec["apt-get update"],
}

service { "apache2":
    enable => true,
	ensure => running,
	#hasrestart => true,
	#hasstatus => true,
	require => Package["apache2"],
}

file { "/var/www/jbs-dev":
	ensure => "link",
	target => "/vagrant/jbs-dev",
	require => Package["apache2"],
	notify => Service["apache2"],
}