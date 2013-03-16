#!/bin/bash
# prepares a new machine to be used

if [ $# -ne 1 ]; then
    echo "Usage: $0 <hostname>"
    exit
fi

HOSTNAME="root@$1"

echo "Now installing status to $HOSTNAME. You will need to enter the root password twice."

# create .ssh and wipe out any existing keys files
# (aggressively wipe out the keys file incase it was preinstalled there by the host..)
ssh $HOSTNAME "mkdir -p .ssh && rm .ssh/authorized_keys*"

# add the public key for the private key shared by status instances
ssh $HOSTNAME "echo 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDW6BKj8WpOp5lRP0PFbgV8a2SUld3n9mRV3dlV0eqihi3khZANpqeBEFnL0QgkEBwgIeGpHM6G7a0QZhMyk+kVj5QtoyZmBEmkRFQBe8HgDqdkjByR6kBBlkgTT+5+CK5nIJeUsQDwWkiLkMiLocVcMzraQLZ16H0NTBu0nWEONINpopCG3MzBp05Qw0RcLMhivUu7X+jdoH8pOUowK8VkhH3IIEAx/ZcAYpS805aFnxBJaHwEJGjZXtqsG/pjPQ3Je8H/MTFxfSml4A7vUo4CaDGMeVAlwGrnS0rdsLls2V9FiLXUnZ+8TUePnJBlPCvK6uvKpz6dQ1P3hWnjqFU3 hidendra@shiro' >> .ssh/authorized_keys"

# now we have automatic logins past here

# add my personal public key
ssh $HOSTNAME "echo 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEA5sHGJgJr57hn/qZ0tABZDHiSp9NqCRy2iCbzxKeRSUH2OTetZf+kt06jRF7l7/4BBPwJlmMFBJBngFGTds/5Fa8UTZfesMBVvU8ckkEUIlqb1x3x9k3BJ2T7Hrsj7Lv5JAolYQbxM2830PvEM1btlNdgGfQJBbMt0xFybq48c2L/CMgkY/KzmihkoVmamcIvYtusSVqXdlDzExEXYNuNzjlJjq1jh54lT6DnfRzB917/LI6Nn7ncKGCmZmOESXNqN+xaaYQxe3RtPHrG/SXbR7HfIgzII8hsNmZk8ligkxFeBMZglgQyOBjuAtoJwxse690kQ2cG/ASWOPCys+4qnQ==' >> .ssh/authorized_keys"

echo "Installed. Other necessary packages may need manual installation."