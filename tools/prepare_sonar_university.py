"""Prepare the current checkout for the university Docker-based Sonar job."""

import json
import os
import socket
import subprocess
from pathlib import Path


def docker_output(*arguments):
    return subprocess.check_output(["docker", *arguments], text=True).strip()


def current_mounts():
    hostname = socket.gethostname()
    try:
        return json.loads(docker_output("inspect", "--format", "{{json .Mounts}}", hostname))
    except subprocess.CalledProcessError:
        for container in docker_output("ps", "-q").splitlines():
            name = docker_output("inspect", "--format", "{{.Config.Hostname}}", container)
            if name == hostname:
                return json.loads(docker_output("inspect", "--format", "{{json .Mounts}}", container))
    raise RuntimeError("No se pudo identificar el contenedor de este job.")


def main():
    checkout = Path(os.environ["CI_PROJECT_DIR"]).resolve()
    matches = []
    for mount in current_mounts():
        destination = Path(mount["Destination"])
        try:
            relative = checkout.relative_to(destination)
        except ValueError:
            continue
        matches.append((len(destination.parts), Path(mount["Source"]) / relative))
    if not matches:
        raise RuntimeError("El checkout no pertenece a un volumen Docker compartido.")
    workspace = max(matches, key=lambda item: item[0])[1]

    # The engine checks this path inside the runner; the scanner mounts it on
    # the Docker host. An alias makes both refer to this exact checkout.
    if workspace != checkout and not workspace.exists():
        workspace.parent.mkdir(parents=True, exist_ok=True)
        workspace.symlink_to(checkout, target_is_directory=True)

    image = os.environ.get("SONAR_IMAGE", "sonarsource/sonar-scanner-cli:latest")
    subprocess.run(
        ["docker", "run", "--rm", "--mount", f"type=bind,source={workspace},target=/usr/src,readonly",
         "--entrypoint", "sh", image, "-c",
         "test -f /usr/src/composer.json && test -d /usr/src/tests && test -f /usr/src/coverage/clover.xml"],
        check=True,
    )
    folder = Path(os.environ["FRAMEWORK_METADATA"]) / "builder"
    folder.mkdir(parents=True, exist_ok=True)
    plan = {
        "project": {"name": "Noticias", "key": "FredyC-noticias"},
        "modules": [{"workspace": str(workspace)}],
    }
    (folder / "build-plan.json").write_text(json.dumps(plan), encoding="utf-8")
    print(f"Checkout verificado: {checkout}")
    print(f"Ruta compartida con SonarScanner: {workspace}")


if __name__ == "__main__":
    main()
