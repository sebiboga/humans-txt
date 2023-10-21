function checkHumansTxt(domain) {
  // Ensure the domain starts with 'http://' or 'https://'
  if (!domain.startsWith('http://') && !domain.startsWith('https://')) {
    domain = 'https://' + domain;
  }

  const humansTxtURL = `${domain}/humans.txt`;

  // Send an HTTP HEAD request to the humans.txt file
  fetch(humansTxtURL, { method: 'HEAD' })
    .then(response => {
      if (response.status === 200) {
        console.log(`The humans.txt file exists at ${humansTxtURL}`);
      } else {
        console.log(`The humans.txt file does not exist at ${humansTxtURL}`);
      }
    })
    .catch(error => {
      console.error(`Error checking humans.txt: ${error}`);
    });
}

// Usage: Pass the domain as a parameter to the function
const domainToCheck = 'https://example.com';
checkHumansTxt(domainToCheck);
