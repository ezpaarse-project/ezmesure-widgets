.PHONY: help
.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

config: ## patch the shibboleth2.xml config file with the SP entityID
	sed -e "s|{{SHIBBOLETH_ENTITY_ID}}|${SHIBBOLETH_ENTITY_ID}|" ./rp/shibboleth/shibboleth2.dist.xml > ./rp/shibboleth/shibboleth2.xml

start: ## start ezMESURE Widgets
	docker-compose up -d

stop: ## stop ezMESURE Widgets
	docker-compose stop

cleanup: ## stop and remove all docker containers
	docker-compose down
